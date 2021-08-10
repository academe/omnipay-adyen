<?php

namespace Omnipay\Adyen;

use Omnipay\Common\AbstractGateway as CommonAbstractGateway;
use Omnipay\Adyen\Traits\GatewayParameters;
use Omnipay\Adyen\Message\FetchPaymentMethodsRequest;
use Omnipay\Adyen\Message\AuthorizeRequest;
use Omnipay\Adyen\Message\CompleteAuthorizeRequest;
use Omnipay\Adyen\Message\CseClientRequest;

abstract class AbstractGateway extends CommonAbstractGateway
{
    use GatewayParameters;

    /**
     *
     */
    public function getDefaultParameters()
    {
        return [
            'merchantAccount' => '',
            'skinCode' => null,
            'secret' => '',
            'apiKey' => 'AQFEhmfxKYLOYhFCw0m/n3Q5qf3VY45bBJ1kV2pEynmlmmNZqcJ0I8ltEyNoAf7r8Km7vnJJWdFEMesxJMLukmQ+F0WAzS8QwV1bDb7kfNy1WIxIIkxgBw==-Oa33c1p6FNEEU0LtFmDCGNCf9x65Rrcxzg220ucPSpQ=-t}<=<$S4a$>jVkY$',
            'currency' => 'EUR',
            'publicKeyToken' => '',             // publicKeyToken aka Library Token.
            'username' => '',
            'password' => '',
            'testMode' => true,
            'shopperLocale' => 'nl_NL',
            'countryCode' => 'nl'
        ];
    }
}
