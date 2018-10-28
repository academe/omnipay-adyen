<?php

namespace Omnipay\Adyen\Message\Hpp;

/**
 * Send the user to the Hosted Payment Page to authorize their payment.
 */

use Omnipay\Adyen\Message\AbstractHppRequest;

class AuthorizeRequest extends AbstractHppRequest
{
    /**
     * No services accessed here.
     */
    public function getEndpoint($service = null)
    {
        return null;
    }

    /**
     * @inherit
     */
    public function getData()
    {
        $data = [];

        // Mandatory base data.

        $data['currencyCode'] = $this->getCurrency();
        $data['paymentAmount'] = $this->getAmountInteger();
        $data['merchantReference'] = $this->getTransactionId();
        $data['skinCode'] = $this->getSkinCode();
        $data['merchantAccount'] = $this->getMerchantAccount();
        $data['sessionValidity'] = $this->getSessionValidity();

        if ($card = $this->getCard()) {
            $data['billingAddress.houseNumberOrName'] = $card->getBillingAddress1() ?: '';
            $data['billingAddress.street'] = $card->getBillingAddress2() ?: '';
            $data['billingAddress.city'] = $card->getBillingCity() ?: '';
            $data['billingAddress.stateOrProvince'] = $card->getBillingState() ?: '';
            $data['billingAddress.country'] = $card->getBillingCountry() ?: '';
            $data['billingAddress.postalCode'] = $card->getBillingPostCode() ?: '';

            if ($shopperEmail = $card->getEmail()) {
                $data['shopperEmail'] = $shopperEmail;
            }
        }

        // billingAddressType:
        //    Not supplied - modifiable / visible
        //    1 - unmodifiable / visible
        //    2 - unmodifiable / invisible

        if ($this->getAddressHidden()) {
            // If it is hidden, then it MUSt be locked too.
            $billingAddressType = '2';
        } elseif ($this->getAddressLocked()) {
            // It's not hidden so it MAY be locked.
            $billingAddressType = '1';
        } else {
            // Not locked nor hidden.
            // Some documentation pages specify this should be '0' and others as ''.
            $billingAddressType = '';
        }

        $data['billingAddressType'] = $billingAddressType;

        // TODO:
        // merchantReturnData countryCode
        // metadata (key and value list)
        // offset (int)
        // orderData (html)
        // shipBeforeDate (listed as mandatory, but seems to be optional)
        // shopperStatement (multiline, supports sme placeholders)

        if ($customerId = $this->getCustomerId()) {
            $data['shopperReference'] = $customerId;
        }

        if ($returnUrl = $this->getReturnUrl()) {
            $data['resURL'] = $returnUrl;
        }

        if ($shopperLocale = $this->getShopperLocale()) {
            $data['shopperLocale'] = $shopperLocale;
        }

        if ($brandCode = $this->getPaymentMethod()) {
            // A single card brand with an optional issuer can be specified.
            // This uses the "skipDetails" entrypoint in the redirect response.

            $data['brandCode'] = $brandCode;

            if ($issuerId = $this->getIssuer()) {
                $data['issuerId'] = $issuerId;
            }
        } else {
            // The brands presented are filterewd by the "allowed" or 'blocked"
            // lists (or both, which makes little sense, but is allowed).

            if ($this->getAllowedMethods() !== null) {
                $data['allowedMethods'] = $this->getAllowedMethods();
            }

            if ($this->getBlockedMethods() !== null) {
                $data['blockedMethods'] = $this->getBlockedMethods();
            }
        }

        // Finally add the HMAC signature for the data.

        $signingString = $this->getSigningString($data);
        $data['merchantSig'] = $this->generateSignature(
            $signingString,
            $this->getSecret()
        );

        return $data;
    }

    /**
     * @inherit
     */
    public function sendData($data)
    {
        // The response is a redirect.

        return new AuthorizeResponse($this, $data);
    }

    /**
     * addressLocked - determins whether the address can be modidied
     * by the user/shopper.
     */
    public function getAddressLocked()
    {
        return $this->getParameter('addressLocked');
    }

    /**
     * @param mixed $value true or equivalent to lock the address
     */
    public function setAddressLocked($value)
    {
        return $this->setParameter('addressLocked', $value);
    }

    /**
     * addressHidden - determins whether the address can be seen
     * by the user/shopper.
     */
    public function getAddressHidden()
    {
        return $this->getParameter('addressHidden');
    }

    /**
     * @param mixed $value true or equivalent to hide the address
     */
    public function setAddressHidden($value)
    {
        return $this->setParameter('addressHidden', $value);
    }
}
