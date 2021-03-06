<?php
/**
 *
 * Abstract subscription class to answer to the TargetSMS server.
 * 
 * Documentation can be found @ http://www.targetsms.nl/docs/TS001-NonSubscription.pdf.
 * 
 * Send back each message trough POST to the following location:
 *  http://api.targetsms.nl/ps_handler/sendmessagev2.asp
 *  
 * Example of complete POST request (non-subscription):
 *  http://api.targetsms.nl/ps_handler/sendmessagev2.asp?
 *      username=test
 *      &handle=d6861a8f6697a28fe7d92c586178ce55
 *      &shortkey=TEST&shortcode=3010
 *      &sendto=31612345678
 *      &mo_messageid=32479824397
 *      &message=This+is+a+test
 *      &tariff=025
 *      &returnid=yes
 *
 * @author Michal Skrzypecki
 */

namespace TargetPay\Sms\Subscriptions;

use TargetPay\Sms;

abstract class AbstractSubscription
{
    
/******************** VARIABLES ********************/
    
    /**
     * Location to the TargetSMS api.
     * @var unknown
     */
    protected $_targetSmsApi = 'http://api.targetsms.nl/ps_handler/sendmessagev2.asp';
    
    /**
     * TargetSMS username.
     * The username of your TargetSMS account
     * @var Alphanumeric
     */
    protected $_username;
    
    /**
     * Access code.
     * Hash ID that belongs to your TargetSMS account. This is not your TargetSMS
     * login password. You can find it on www.targetsms.nl/handle (after log in)
     * @var Alphanumeric
     */
    protected $_handle;
    
    /**
     * Keyword.
     * @var varchar
     */
    protected $_shortKey;
    
    /**
     * SMS shortcode
     * @var number
     */
    protected $_shortCode;
    
    /**
     * Recipient's phone number
     * @var number
     */
    protected $_sendTo;
    
    /**
     * Message ID of the incoming message.
     * @var number
     */
    protected $_moMessageId;
    
    /**
     * Amount to be billed.
     * The rate that will be charged to the end-user for receiving the message.
     * The rate is in cents, local currency and must be equal to or lower than
     * the rate you set when registering the keyword.
     * @var number
     */
    protected $_tariff;
    
    /**
     * WAP push message
     * @var number
     */
    protected $_push;
    
    /**
     * WAP push URL.
     * @var varchar
     */
    protected $_purl;
    
    /**
     * Text to send back to the receiver.
     * @var unknown
     */
    protected $_text;
    
    /**
     * The buil query send to the TargetPay api.
     * @var unknown
     */
    protected $_query;
    
    /**
     * Array with all the query data to use in http_build_query.
     */
    protected $_queryData = array();
    
    /**
     * Return message id in the response.
    protected $_queryData = array();
     * Add this parameter with value ‘yes’ when you want to receive the MT message ID in the response.
     * You may use this ID in receiving your billing status.
     * @var varchar
     */
    protected $_returnId = 'yes';
    
    /**
     * Response errors from the TargetSMS api.
     * @var unknown
     */
    protected $_errors = array(
        45001 => 'Incorrect phone number',
        45002 => 'Incorrect message',
        45003 => 'Incorrect shortcode',
        45004 => 'Incorrect mo_messageid',
        45005 => 'MO record not found for this mo_messageid or maximum number of messages exceeded',
        45006 => 'Invalid end-user rate (tariff)',
        45007 => 'Username doesn\'t match keyword owner',
        45013 => 'End-user rate (tariff) was higher than the registered price',
        45019 => 'Unknown member',
        45020 => 'Maximum number of messages exceeded',
        45022 => 'Incorrect URL',
        45023 => 'WAP description too long',
        45024 => 'URL too long',
        45025 => 'End-user rate must be zero (000) when sending a WAP Push message in this country',
        45030 => 'No handle specified',
        45031 => 'Incorrect handle',
        45091 => 'Blocked by infofilter',
        45092 => 'Blocked by infofilter',
    );
    
    /**
     * Respons from the TargetSMS api.
     * @var unknown
     */
    protected $_response;
    
    /**
     * The complete api url with query attached.
     * @var unknown
     */
    public $apiUrl;
    
    /**
     * Test option.
     * @var unknown
     */
    public $test = 0;

/******************** METHODS ********************/
        
    /**
     * Construct the object.
     * @param TargetPay\Sms\Reciever    $receiver
     * @param TargetPay\Sms\User        $username
     * @param number                    $tarif
     */
    public function __construct(Sms\Handler\AbstractHandler $handler,
                                Sms\User $user,
                                $tariff,
                                $text)
    {
        //TargetSMS mandatory fields.
        $this->_username    = $user->getUsername();
        $this->_handle      = $user->getHandle();
        $this->_shortKey    = $handler->getMoShortKey();
        $this->_shortCode   = $handler->getShortCode();
        $this->_sendTo      = $handler->getSendTo();
        $this->_moMessageId = $handler->getMoMessageId();
        $this->_tariff      = $tariff;
        $this->_text        = $this->cleanText($text);
    }
    
    /**
     * Set the query data for the TargetSMS api.
     */
    public function setQuery()
    {
        if (empty($this->_queryData)) {
            throw new \Exception('Query data is not set.');
            return;
        }
        
        $this->_query = http_build_query($this->_queryData, '', '&');
    }
    
    /**
     * Get the valid build query.
     * @return string
     */
    public function getQuery()
    {
        return $this->_query;
    }
    
    /**
     * Set complete build api url.
     */
    public function setApiUrl()
    {
        $this->apiUrl = rtrim($this->_targetSmsApi, '?') . '?' . $this->getQuery();
        
        return $this;
    }
    
    /**
     * Get complete build api url.
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }
    
    /**
     * Set the tariff to be payed. Value needs to be in cents.
     * @param number $int
     */
    public function setTariff($int = 0)
    {
        $this->_tariff = $int;
        return $this;
    }
    
    /**
     * Get the TargetSMS api.
     * @return \TargetPay\Sms\unknown
     */
    public function getTargetSmsApi()
    {
        return $this->_targetSmsApi;
    }
    
    /**
     * Set the message.
     * @param string $msg
     */
    public function setText($msg = '')
    {
        $msg = $this->cleanText($msg);
        $this->_text = $msg;
    }

    /**
     * Remove characters not allowed by TargetSMS. This is the message send to
     * TargetSMS api which then will be send to the receiver.
     * @param string $msg
     * @return mixed
     */
    public function cleanText($msg = '')
    {
        return preg_replace('/[^a-z \d\+\%\#\(\)\*\+\,\.\-\/\:\;\<\=\>\?\_£¥§ÄÅÜäèéìñòöùü\]]/i', '', $msg);
    }
    
    /**
     * Check if curl is installed.
     * @return boolean
     */
    public function isCurl()
    {
        return function_exists('curl_version');
    }
    
    /**
     * Iniate curl. Set the _response.
     * @param unknown $apiUrl
     * @return \TargetPay\Sms\Subscriptions\unknown
     */
    public function initCurl()
    {
        $api     = $this->apiUrl;
        $ch      = curl_init();
        $headers = array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.8) Gecko/20061025 Firefox/1.5.0.8");
        
        //curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $this->_response = curl_exec($ch);
        curl_close($ch);
        
        return $this->_response;
    }
    
    /**
     * Iniate the object.
     */
    public function init()
    {
        $response = null;
        
        if ($this->isCurl()) {
            $response = $this->initCurl();
        } else {
            throw new \Exception('No method found to initiate the Api call with.');
        }
        
        return $response;
    }
    
    /**
     * Get the error message by code number.
     * @param number $errorCode
     * @return \TargetPay\Sms\Subscriptions\unknown
     */
    public function getErrorMessage($errorCode = 0)
    {
        if (isset($this->_errors[$errorCode])) {
            return $this->_errors[$errorCode];
        }
    }
    
    /**
     * Get the response from TargetSMS api.
     * @return \TargetPay\Sms\Subscriptions\unknown
     */
    public function getResponse()
    {
        return $this->_response;
    }
    
    /**
     * To string method for logging purposses.
     * @return string
     */
    public function __toString()
    {
        $vars = get_object_vars($this);
        $str  = '';
        
        foreach ($vars as $var => $val) {
            $str .= $var .' : '. $val . PHP_EOL;
        }
        
        return $str;
    }
}