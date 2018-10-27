<?php

namespace Omnipay\Adyen\Message\Cse;

/**
 * Authorize a payment.
 */

use InvalidArgumentException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Adyen\Message\Api\AuthorizeRequest as ApiAuthorizeRequest;

class CompleteAuthorizeRequest extends ApiAuthorizeRequest
{
    protected $endpointService = 'authorise3d';

    public function getData()
    {
        $this->validate('merchantAccount');

        var_dump($this->httpRequest);
    }
}
