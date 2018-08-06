<?php

namespace Omnipay\Adyen\Message\Api;

use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Adyen\Traits\DataWalker;

class AuthorizeResponse extends AbstractResponse
{
    use DataWalker;

    protected $payload;

    public function isSuccessful()
    {
        // TODO
    }

    /**
     * The first time we get data, expand any dot-notation keys to
     * ested arrays, then cache the result for subsequent access.
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
     * @return int|null
     */
    public function getFraudScore()
    {
        return $this->getDataItem('fraudResult.accountScore');
    }

    /**
     * @return array
     */
    public function getFraudResults()
    {
        return $this->getDataItem('fraudResult.results', []);
    }

    /**
     * @return string|null
     */
    public function getAuthCode()
    {
        return $this->getDataItem('authCode');
    }

    /**
     * @return string|null
     */
    public function getResultCode()
    {
        return $this->getDataItem('resultCode');
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
     * Get raw card expiryDate in MM/YYYY format.
     * @return string|null
     */
    public function getRawExpiryDate()
    {
        return $this->getDataItem('additionalData.expiryDate');
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

    /**
     * Get the last four digits of the credit card.
     *
     * @return string|null
     */
    public function getCardSummary()
    {
        return $this->getDataItem('additionalData.cardSummary');
    }

    public function getNumberLastFour()
    {
        return $this->getCardSummary();
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

    /**
     * @return string|null alias for the cardholder name
     */
    public function getName()
    {
        return $this->getCardHolderName();
    }
}
