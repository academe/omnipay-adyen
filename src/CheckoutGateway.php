<?php

namespace Omnipay\Adyen;

/**
 * Adyen Checkout Process Gateway
 */

use Omnipay\Adyen\Message\Checkout\AuthorizeRequest;
use Omnipay\Adyen\Message\Checkout\CompleteAuthorizeRequest;
use Omnipay\Adyen\Message\Checkout\CreateCardRequest;
use Omnipay\Adyen\Message\Checkout\PaymentMethodRequest;
use Omnipay\Common\PaymentMethod;

class CheckoutGateway extends ApiGateway
{
    public function getName()
    {
        return 'Adyen Checkout';
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Adyen\Message\Api\AuthorizeRequest
     */
    public function authorize(array $parameters = array())
    {
        return $this->createRequest(AuthorizeRequest::class, $parameters);
    }

    /**
     * Helper for generating data needed in a dropin form.
     */
    public function paymentMethods(array $parameters = array())
    {
        return $this->createRequest(PaymentMethodRequest::class, $parameters);
    }

    /**
     * Helper for generating a credit card token
     */
    public function createCard(array $parameters = array())
    {
        return $this->createRequest(CreateCardRequest::class, $parameters);
    }

    public function completeAuthorize(array $parameters = array())
    {;
        return $this->createRequest(CompleteAuthorizeRequest::class, $parameters);
    }
}
