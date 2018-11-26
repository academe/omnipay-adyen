<?php

namespace Omnipay\Adyen\Message\Cse;

/**
 * Authorize a payment.
 */

use InvalidArgumentException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Adyen\Message\Api\AuthorizeRequest as ApiAuthorizeRequest;
use Omnipay\Adyen\Message\AbstractRequest;

class CompleteAuthorizeRequest extends ApiAuthorizeRequest
{
    public function getEndpoint()
    {
        return $this->getPaymentUrl(
            AbstractRequest::SERVICE_GROUP_PAYMENT_AUTHORISE3D
        );
    }

    public function getData()
    {
        $this->validate('merchantAccount');

        $data = [
            'merchantAccount' => $this->getMerchantAccount(),
            'md' => $this->getMd(),
            'paResponse' => $this->getPaResponse(),
        ];

        if ($shopperIp = $this->getShopperIp()) {
            $data['shopperIP'] = $shopperIp;
        }

        return $data;
    }

    /**
     * Use of the ShopperIp is strongly recommended.
     */
    public function getShopperIp()
    {
        return $this->getParameter('shopperIp');
    }

    public function setShopperIp($value)
    {
        return $this->setParameter('shopperIp', $value);
    }

    /**
     * Payment session for 3D Secure.
     */
    public function getMd()
    {
        return $this->getParameter('md')
            ?: $this->httpRequest->request->get('MD');
    }

    public function setMd($value)
    {
        return $this->setParameter('md', $value);
    }

    /**
     * For 3D Secure.
     * Returns the PA Result from the POSTed in the current request,
     * with the ability to override the POST if it is available from
     * another source.
     */
    public function getPaResponse()
    {
        return $this->getParameter('paResponse')
            ?: $this->httpRequest->request->get('PaRes');
    }

    public function setPaResponse($value)
    {
        return $this->setParameter('paResponse', $value);
    }
}
