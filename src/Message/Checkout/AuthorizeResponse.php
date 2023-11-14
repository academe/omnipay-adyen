<?php

namespace Omnipay\Adyen\Message\Checkout;

use Omnipay\Adyen\Traits\DataWalker;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class AuthorizeResponse extends AbstractResponse implements RedirectResponseInterface
{
    use DataWalker;

    /**
     * @var valus for the resultCode.
     */
    const RESULT_CODE_AUTHORISED = "Authorised";
    const RESULT_CODE_REFUSED = "Refused";
    const RESULT_CODE_ERROR = "Error";
    const RESULT_CODE_CANCELLED = "Cancelled";
    const RESULT_CODE_RECEIVED = "Received";
    const RESULT_CODE_REDIRECTSHOPPER = "RedirectShopper";

    const SECURE_3D_SMS_VERIFICATION = 'CUPSecurePlus-CollectSMSVerificationCode';

    protected $payload;

    public function isSuccessful()
    {
        return $this->getResultCode() === static::RESULT_CODE_AUTHORISED
            && !$this->getMessage();
    }

    /**
     * @return string|null
     */
    public function getResultCode()
    {
        return $this->getDataItem('resultCode');
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->getDataItem('refusalReason');
    }

    public function isCancelled()
    {
        return $this->getResultCode() === static::RESULT_CODE_CANCELLED;
    }

    /**
     * May involve a redirect for 3D Secure.
     */
    public function isRedirect()
    {
        return $this->getResultCode() === static::RESULT_CODE_REDIRECTSHOPPER;
    }

    /**
     * May involve a redirect for payment mehtods which need redirect.
     */
    public function getRedirectUrl()
    {
        return $this->getDataItem('action')['url'];
    }

    /**
     * Returns data for POST redirecting
     */
    public function getRedirectData()
    {
        return $this->getDataItem('action')['redirectData'];
    }

    /**
     * For 3D Secure or other payment types that require redirects.
     */
    public function getRedirectMethod()
    {
        return $this->getDataItem('action')['method'];
    }

    /**
     * The first time we get data, expand any dot-notation keys to
     * nested arrays, then cache the result for subsequent access.
     */
    public function getData()
    {
        if ($this->payload === null) {
            $this->payload = parent::getData();

            if (is_array($this->payload)) {
                $this->payload = $this->expandKeys($this->payload);
            }

        }

        return $this->payload;
    }

    public function getTransactionReference()
    {
        return $this->getDataItem('pspReference');
    }

    /**
     * @return string|null
     */
    public function getCode()
    {
        return $this->getResultCode();
    }

    /**
     * Get paymentMethod.
     * @return string|null
     */
    public function getPaymentMethod()
    {
        return $this->getDataItem('additionalData.paymentMethod');
    }

    /**
     * Get raw card expiry month in MM format.
     * @return string|null
     */
    public function getExpiryMonth()
    {
        $expiryDate = $this->getRawExpiryDate();

        if (strpos($expiryDate, '/') !== false) {
            list($month, $year) = explode('/', $expiryDate);

            return $month;
        }
    }

    /**
     * Get raw card expiryDate in MM/YYYY format.
     * @return string|null
     */
    public function getRawExpiryDate()
    {
        return $this->getDataItem('additionalData.expiryDate');
    }

    /**
     * Get raw card expiry year in YYYY (four digit) format.
     * @return string|null
     */
    public function getExpiryYear()
    {
        $expiryDate = $this->getRawExpiryDate();

        if (strpos($expiryDate, '/') !== false) {
            list($month, $year) = explode('/', $expiryDate);

            return $year;
        }
    }

    public function getNumberLastFour()
    {
        return $this->getCardSummary();
    }

    /**
     * Get the last four digits of the credit card.
     *
     * @return string|null
     */
    public function getCardSummary()
    {
        return $this->getDataItem('additionalData.cardSummary');
    }

    /**
     * @return string|null alias for the cardholder name
     */
    public function getName()
    {
        return $this->getCardHolderName();
    }

    /**
     * Get the name of the credit card holder.
     *
     * @return string|null
     */
    public function getCardHolderName()
    {
        return $this->getDataItem('additionalData.cardHolderName');
    }

    public function getDetails()
    {
        return $this->getDataItem('details');
    }

    public function getPaymentData()
    {
        return $this->getDataItem('action.paymentData');
    }
}
