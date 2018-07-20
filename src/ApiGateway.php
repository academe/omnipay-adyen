<?php

namespace Omnipay\Adyen;

/**
 * Adyen API (direct API) Gateway.
 */

//use Omnipay\Adyen\Message\Hpp\FetchPaymentMethodsRequest;
//use Omnipay\Adyen\Message\Hpp\AuthorizeRequest;
//use Omnipay\Adyen\Message\Hpp\CompleteAuthorizeRequest;
use Omnipay\Adyen\Message\Api\CaptureRequest;

class ApiGateway extends AbstractGateway
{
    public function getName()
    {
        return 'Adyen API';
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Adyen\Message\Api\CaptureRequest
     */
    public function authorize(array $parameters = array())
    {
        return $this->createRequest(CaptureRequest::class, $parameters);
    }
}
