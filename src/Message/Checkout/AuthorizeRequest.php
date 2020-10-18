<?php

namespace Omnipay\Adyen\Message\Checkout;

/**
 * Cancel an auathorised payment.
 */

use Omnipay\Adyen\Message\AbstractCheckoutRequest;
use Omnipay\Adyen\Message\AbstractRequest;
use Omnipay\Common\Exception\InvalidRequestException;

class AuthorizeRequest extends AbstractCheckoutRequest
{
    public function createResponse($data)
    {
        return new AuthorizeResponse($this, $data);
    }

    public function getEndpoint()
    {
        return $this->getCheckoutUrl(
            AbstractRequest::SERVICE_GROUP_PAYMENT_PAYMENTS
        );
    }

    public function getData()
    {
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
            'shopperInteraction' => 'Ecommerce',
        ];

        if (!empty($this->getShopperReference())) {
            $data['shopperReference'] = $this->getShopperReference();
        }
        if (!empty($this->getReturnUrl())) {
            $data['returnUrl'] = $this->getReturnUrl();
        }
        if (!empty($this->getClientIp())) {
            $data['shopperIP'] = $this->getClientIp();
        }
        if (!empty($this->getBrowserInfo())) {
            $data['browserInfo'] = $this->getBrowserInfo();
        }
        if (!empty($this->getOrigin())) {
            $data['origin'] = $this->getOrigin();
        }

        $data = $this->addPaymentMethodData($data);

        if (isset($data['paymentMethod']['storedPaymentMethodId'])) {
            $data['recurringProcessingModel'] = 'CardOnFile';
            $data['shopperInteraction'] = 'ContAuth';
        }

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
        if (count($this->getPaymentMethod()) > 0) {
            return ['paymentMethod' => $this->getPaymentMethod()];
        } else {
            return [];
        }
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
}
