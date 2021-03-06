<?php
/**
 * Non subscription class for TargetPay SMS.
 * 
 * @author Michal Skrzypecki
 */

namespace TargetPay\Sms\Subscriptions;

use TargetPay\Sms\Subscriptions\AbstractSubscription;

class NonSubscription extends AbstractSubscription
{    
    /**
     * Set data for the TargetSMS api.
     * @return \TargetPay\Sms\Subscriptions\NonSubscription
     */
    public function setQuery() {
        $this->_queryData = array(
            'shortkey'      => $this->_shortKey,
            'shortcode'     => $this->_shortCode,
            'sendto'        => $this->_sendTo,
            'mo_messageid'  => $this->_moMessageId,
            'message'       => $this->_text,
            'tariff'        => $this->_tariff,
            'test'          => $this->test,
        );
        
        parent::setQuery();
        
        return $this;
    }
}