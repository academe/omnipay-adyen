<?php

namespace Omnipay\Adyen;

/**
 * Adyen CSE (Client-Side Encryption) Gateway.
 * Keeps the end user on the merchant site. Appears to be cards only.
 * See https://docs.adyen.com/developers/ecommerce-integration/cse-integration-ecommerce
 */

use Omnipay\Adyen\Message\FetchPaymentMethodsRequest;
use Omnipay\Adyen\Message\Cse\AuthorizeRequest;
use Omnipay\Adyen\Message\Cse\CompleteAuthorizeRequest;
use Omnipay\Adyen\Message\Cse\EncryptionClientRequest;

class CseGateway extends ApiGateway
{
    public function getName()
    {
        return 'Adyen CSE';
    }

    /**
     * Request used to generate the encryption form.
     */
    public function encryptionClient(array $parameters = [])
    {
        return $this->createRequest(EncryptionClientRequest::class, $parameters);
    }

    /**
     * Authorize a payment using the encrypeted cardReference.
     */
    public function authorize(array $parameters = array())
    {
        return $this->createRequest(AuthorizeRequest::class, $parameters);
    }

    /**
     * Complete an authorization after return from 3D-Secure.
     */
    public function completeAuthorize(array $parameters = array())
    {
        return $this->createRequest(CompleteAuthorizeRequest::class, $parameters);
    }
}
