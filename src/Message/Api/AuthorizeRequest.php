<?php

namespace Omnipay\Adyen\Message\Api;

/**
 * Cancel an auathorised payment.
 */

use Omnipay\Adyen\Message\AbstractApiRequest;
use Omnipay\Common\Exception\InvalidRequestException;

class AuthorizeRequest extends AbstractApiRequest
{
    protected $endpointService = 'authorise';

    public function createResponse($data)
    {
        return new AuthorizeResponse($this, $data);
    }

    public function getEndpoint($service = null)
    {
        return $this->getPaymentUrl($this->endpointService);
    }

    public function getData()
    {
        $this->validate('amount', 'currency', 'merchantAccount', 'transactionId');

        $additionalData = [];

        $additionalData = array_merge(
            $additionalData,
            $this->getPaymentMethodData()
        );

        $amount = [
            'value' => $this->getAmountInteger(),
            'currency' => $this->getCurrency(),
        ];

        $data = [
            'additionalData' => $additionalData,
            'amount' => $amount,
            'reference' => $this->getTransactionId(),
            'merchantAccount' => $this->getMerchantAccount(),
        ];

        return $data;
    }

    /**
     * Get the payment data for the additionalData array.
     *
     * @return array
     * @throws InvalidRequestException
     */
    public function getPaymentMethodData()
    {
        // TODO: data from creditCard or bankAccount
        return [];
    }
}
