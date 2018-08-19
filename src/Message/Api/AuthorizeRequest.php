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

            // TODO: only include if the above mandatory fields are set.

            $additionalData['billingAddress'] = $billingAddress;
        }

        $additionalData['executeThreeD'] = ((bool)$this->get3DSecure() ? 'true' : 'false');

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
     * In this case it is the CC details posted to the merchant site.
     * Be aware of PCI regulations when doing this.
     *
     * @return array
     * @throws InvalidRequestException
     */
    public function getPaymentMethodData()
    {
        $cardData = $this->getCardData();

        if (! empty($cardData)) {
            return ['card' => $cardData];
        }

        $bankData = $this->getBankData();

        if (! empty($bankData)) {
            return ['bankAccount' => $bankData];
        }

        return [];
    }

    /**
     * If a credit card is supplied, then return the credit card
     * data, otherwise an empty array.
     *
     * @return array
     */
    public function getCardData()
    {
        $data = [];

        $card = $this->getCard();

        if ($card) {
            if ($card->getNumber()) {
                // TODO: validate required fields.
                $card->validate();

                $data['number'] = $card->getNumber();
                $data['expiryYear'] = $card->getExpiryDate('Y');
                $data['expiryMonth'] = $card->getExpiryDate('m');
                $data['holderName'] = $card->getName();

                // Optional: cvc issueNumber startMonth startYear
            }
        }

        return $data;
    }

    // TODO
    public function getBankData()
    {
        return [];
    }
}
