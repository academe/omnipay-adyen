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

        // additionalData is populated from a number of sources.

        $additionalData = [];

        // Merge in the payment method details (CC number, encrypted card, etc.)

        $additionalData = array_merge(
            $additionalData,
            $this->getPaymentMethodData()
        );

        // Billing address, if supplied.

        if ($card = $this->getCard()) {
            // The gateway ideally needs address1 split into separate
            // houseNumberOrName and street fields. There is no attempt
            // to do that here, and some testing is needed to see what
            // happens if a standard address1 is not split.

            $billingAddress = [
                // Mandatory fields:
                'city' => $card->getBillingCity(),
                'country' => $card->getBillingCountry(),
                'houseNumberOrName' => $card->getBillingAddress1(),
                'street' => $card->getBillingAddress2(),
                // Optional fields:
                'postalCode' => $card->getBillingPostcode(),
                'stateOrProvince' => $card->getBillingState(),
            ];

            // TODO: only include if the mandatory fields are set.

            $additionalData['billingAddress'] = $billingAddress;
        }

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
