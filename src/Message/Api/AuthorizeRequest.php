<?php

namespace Omnipay\Adyen\Message\Api;

/**
 * Cancel an auathorised payment.
 */

use Omnipay\Adyen\Message\AbstractApiRequest;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Adyen\Message\AbstractRequest;

class AuthorizeRequest extends AbstractApiRequest
{
    public function createResponse($data)
    {
        return new AuthorizeResponse($this, $data);
    }

    public function getEndpoint()
    {
        return $this->getPaymentUrl(
            AbstractRequest::SERVICE_GROUP_PAYMENT_AUTHORISE
        );
    }

    public function getData()
    {
        // TODO: for API authorize only, we need username and password set
        // to support Basic Auth needed for the API endpoint.

        $this->validate('amount', 'currency', 'merchantAccount', 'transactionId');

        // additionalData is populated from a number of sources.

        $additionalData = [];

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
            'reference' => (string)$this->getTransactionId(),
            'merchantAccount' => $this->getMerchantAccount(),
        ];

        $data = $this->addPaymentMethodData($data);

        return $data;
    }

    /**
     * Merge the payment informatino data into the data array.
     * For the API (direct) authorise, the data is merged into
     * the root level, since it is mandatory.
     *
     * @param array $data
     * @return array
     */
    public function addPaymentMethodData(array $data)
    {
        // Merge in the payment method details (CC number, encrypted card, etc.)

        return array_merge(
            $data,
            $this->getPaymentMethodData()
        );
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

                if ($cvv = $card->getCvv()) {
                    $data['cvc'] = $cvv;
                }

                // Optional: issueNumber, startMonth, startYear
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
