<?php

namespace Omnipay\Adyen;

/**
 * Adyen HPP (Hosted Payment Pages) Gateway.
 * Takes the user off the merchant site, but offers many local payment methods.
 * See https://docs.adyen.com/developers/ecommerce-integration/cse-integration-ecommerce
 */

use Omnipay\Adyen\Message\Hpp\FetchPaymentMethodsRequest;
use Omnipay\Adyen\Message\Hpp\AuthorizeRequest;
use Omnipay\Adyen\Message\Hpp\CompleteAuthorizeRequest;

class HppGateway extends ApiGateway
{
    public function getName()
    {
        return 'Adyen HPP';
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Adyen\Message\Hpp\FetchPaymentMethodsRequest
     */
    public function fetchPaymentMethods(array $parameters = [])
    {
        return $this->createRequest(FetchPaymentMethodsRequest::class, $parameters);
    }

    /**
     * Note: this is deliberately the same message as fetchPaymentMethods.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Adyen\Message\Hpp\FetchPaymentMethodsRequest
     */
    public function fetchIssuers(array $parameters = [])
    {
        return $this->createRequest(FetchPaymentMethodsRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Adyen\Message\Hpp\AuthorizeRequest
     */
    public function authorize(array $parameters = array())
    {
        return $this->createRequest(AuthorizeRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Adyen\Message\Hpp\CompleteAuthorizeRequest
     */
    public function completeAuthorize(array $parameters = array())
    {
        return $this->createRequest(CompleteAuthorizeRequest::class, $parameters);
    }
}
