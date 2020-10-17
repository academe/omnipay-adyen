<?php


namespace Omnipay\Adyen\Message\Checkout;


use Omnipay\Adyen\Traits\DataWalker;
use Omnipay\Common\Message\AbstractResponse;

class CompleteAuthorizeResponse extends AbstractResponse
{
    use DataWalker;

    /**
     * @var valus for the resultCode.
     */
    const RESULT_CODE_AUTHORISED        = "Authorised";
    const RESULT_CODE_REFUSED           = "Refused";
    const RESULT_CODE_ERROR             = "Error";
    const RESULT_CODE_CANCELLED         = "Cancelled";
    const RESULT_CODE_RECEIVED          = "Received";

    protected $payload;

    public function isSuccessful()
    {
        return $this->getResultCode() === static::RESULT_CODE_AUTHORISED
            && ! $this->getMessage();
    }

    public function isCancelled()
    {
        return $this->getResultCode() === static::RESULT_CODE_CANCELLED;
    }

    public function getResultCode()
    {
        return $this->getDataItem('resultCode');
    }

    public function getTransactionReference()
    {
        return $this->getDataItem('pspReference');
    }

}