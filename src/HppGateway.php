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
use Omnipay\Adyen\Message\Api\CancelRequest;
use Omnipay\Adyen\Message\Api\CaptureRequest;
use Omnipay\Adyen\Message\Api\RefundRequest;
use Omnipay\Adyen\Message\Api\NotificationRequest;

class HppGateway extends AbstractGateway
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

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Adyen\Message\Api\CancelRequest
     */
    public function void(array $parameters = array())
    {
        return $this->createRequest(CancelRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Adyen\Message\Api\CaptureRequest
     */
    public function capture(array $parameters = array())
    {
        return $this->createRequest(CaptureRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Adyen\Message\Api\RefundRequest
     */
    public function refund(array $parameters = array())
    {
        return $this->createRequest(RefundRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return NotificationRequest
     */
    public function acceptNotification(array $parameters = [])
    {
        return $this->createRequest(NotificationRequest::class, $parameters);
    }
}
