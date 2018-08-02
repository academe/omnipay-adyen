<?php

namespace Omnipay\Adyen;

/**
 * Adyen CSE (Client-Side Encryption) Gateway.
 * Keeps the end user on the merchant site. Appears to be cards only.
 * See https://docs.adyen.com/developers/ecommerce-integration/cse-integration-ecommerce
 */

use Omnipay\Adyen\Message\FetchPaymentMethodsRequest;
use Omnipay\Adyen\Message\AuthorizeRequest;
use Omnipay\Adyen\Message\CompleteAuthorizeRequest;
use Omnipay\Adyen\Message\Cse\ClientRequest;

class CseGateway extends AbstractGateway
{
    public function getName()
    {
        return 'Adyen CSE';
    }

    public function authorize(array $parameters = array())
    {
        return $this->createRequest(AuthorizeRequest::class, $parameters);
    }

    public function encryptionClient(array $parameters = [])
    {
        return $this->createRequest(ClientRequest::class, $parameters);
    }
}
