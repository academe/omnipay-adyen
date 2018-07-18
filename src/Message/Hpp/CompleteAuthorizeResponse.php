<?php

namespace Omnipay\Adyen\Message\Hpp;

/**
 * Complete an HPP Authorize a payment.
 */

use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class CompleteAuthorizeResponse extends AbstractResponse
{
    /**
     * Possible authorisatino result values.
     */
    const AUTHRESULT_AUTHORISED = 'AUTHORISED';
    const AUTHRESULT_CANCELLED  = 'CANCELLED';
    const AUTHRESULT_REFUSED    = 'REFUSED';
    const AUTHRESULT_PENDING    = 'PENDING';
    const AUTHRESULT_ERROR      = 'ERROR';

    public function isSuccessful()
    {
        return $this->getAuthResult() === static::AUTHRESULT_AUTHORISED;
    }

    public function isPending()
    {
        return $this->getAuthResult() === static::AUTHRESULT_PENDING;
    }

    public function isCancelled()
    {
        return $this->getAuthResult() === static::AUTHRESULT_CANCELLED;
    }

    public function getTransactionId()
    {
        return $this->getMerchantReference();
    }

    public function getTransactionReference()
    {
        return $this->getPspReference();
    }

    /**
     * The authResult is the nearest we have to a response code.
     * There are no messages however. They can be fetched from the
     * API separately if needed.
     */
    public function getCode()
    {
        return $this->getAuthResult();
    }

    /**
     * The raw authResult
     */
    public function getAuthResult()
    {
        return $this->getData()['authResult'] ?? null;
    }

    /**
     * The raw merchantReference
     */
    public function getMerchantReference()
    {
        return $this->getData()['merchantReference'] ?? null;
    }

    /**
     * The raw paymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->getData()['paymentMethod'] ?? null;
    }

    /**
     * The raw pspReference
     */
    public function getPspReference()
    {
        return $this->getData()['pspReference'] ?? null;
    }

    /**
     * The raw shopperLocale
     */
    public function getShopperLocale()
    {
        return $this->getData()['shopperLocale'] ?? null;
    }

    /**
     * The raw skinCode
     */
    public function getSkinCode()
    {
        return $this->getData()['skinCode'] ?? null;
    }
}
