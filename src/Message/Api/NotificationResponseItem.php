<?php

namespace Omnipay\Adyen\Message\Api;

/**
 *
 */

use Omnipay\Common\Message\AbstractResponse;
use Money\Money;
use Money\Currency;

class NotificationResponseItem extends AbstractResponse
{
    /**
     *
     */
    public function isSuccessful()
    {
        // TODO
    }

    /**
     *
     */
    public function getAmountInteger()
    {
        return $this->getDataItem('value', $this->getDataItem('amount.value'));
    }

    /**
     *
     */
    public function getCurrencyCode()
    {
        return $this->getDataItem('currency', $this->getDataItem('amount.currency'));
    }

    /**
     *
     */
    public function getAmountMoney()
    {
        $amountInteger = $this->getAmountInteger();
        $currencyCode = $this->getCurrencyCode();

        return new Money($amountInteger, new Currency($currencyCode));
    }

    /**
     *
     */
    public function getEventCode()
    {
        return $this->getDataItem('eventCode');
    }

    /**
     *
     */
    public function getEventDate()
    {
        return $this->getDataItem('eventDate');
    }

    /**
     * aka merchantId?
     */
    public function getMerchantAccountCode()
    {
        return $this->getDataItem('merchantAccountCode');
    }

    /**
     *
     */
    public function getMerchantReference()
    {
        return $this->getDataItem('merchantReference');
    }

    /**
     *
     */
    public function getTransactionId()
    {
        return $this->getMerchantReference();
    }

    /**
     *
     */
    public function getPspReference()
    {
        return $this->getDataItem('pspReference');
    }

    /**
     *
     */
    public function getTransactionReference()
    {
        return $this->getPspReference();
    }

    /**
     * Get a data item using "dot-notation".
     */
    public function getDataItem($key, $default = null)
    {
        $target = $this->getData();

        if (is_null($key) || trim($key) == '') {
            return $target;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
                continue;
            }

            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
                continue;
            }

            if ($default instanceof Closure) {
                return $default();
            } else {
                return $default;
            }
        }

        return $target;
    }
}
