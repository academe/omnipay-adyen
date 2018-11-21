<?php

namespace Omnipay\Adyen;

/**
 * Adyen API (direct API) Gateway.
 */

use Omnipay\Adyen\Message\Api\AuthorizeRequest;
use Omnipay\Adyen\Message\Api\CancelRequest;
use Omnipay\Adyen\Message\Api\CaptureRequest;
use Omnipay\Adyen\Message\Api\CompleteAuthorizeRequest;
use Omnipay\Adyen\Message\Api\EncryptionClientRequest;
use Omnipay\Adyen\Message\Api\RefundRequest;
use Omnipay\Adyen\Message\Api\NotificationRequest;

class ApiGateway extends AbstractGateway
{
    public function getName()
    {
        return 'Adyen API';
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
     * Complete an authorization after return from 3D-Secure.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Adyen\Message\Api\CompleteAuthorizeRequest
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

    /**
     * Request used to generate the encryption form.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function encryptionClient(array $parameters = [])
    {
        return $this->createRequest(EncryptionClientRequest::class, $parameters);
    }
}
