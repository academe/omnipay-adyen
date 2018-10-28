<?php

namespace Omnipay\Adyen\Message\Hpp;

/**
 *
 */

use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\FetchPaymentMethodsResponseInterface;
use Omnipay\Common\Message\FetchIssuersResponseInterface;
use Omnipay\Common\PaymentMethod;
use Omnipay\Common\Issuer;

class FetchPaymentMethodsResponse extends AbstractResponse implements
    FetchPaymentMethodsResponseInterface,
    FetchIssuersResponseInterface
{
    /**
     * @var array
     */
    protected $paymentMethods = [];

    /**
     * @inherit
     */
    public function isSuccessful()
    {
        return !empty($this->paymentMethods);
    }

    /**
     * @return PaymentMethod[]
     */
    public function getPaymentMethods()
    {
        $result = [];

        foreach ($this->getPaymentMethodsAssoc() as $method) {
            $result[] = new PaymentMethod($method['brandCode'], $method['name']);
        }

        return $result;
    }

    /**
     * Return the raw payment methods.
     *
     * @return array List of brandCodes/names/issuers as supplied by the API
     */
    public function getPaymentMethodsRaw()
    {
        if (isset($this->data['paymentMethods'])) {
            return $this->data['paymentMethods'];
        } else {
            return [];
        }
    }

    /**
     * @return array Associative array of brands.
     */
    public function getPaymentMethodsAssoc()
    {
        $result = [];

        foreach ($this->getPaymentMethodsRaw() as $method) {
            if (array_key_exists('brandCode', $method) && array_key_exists('name', $method)) {
                $result[$method['brandCode']] = $method;
            }

            // If there are issuers then give them the associative key treatment too.

            if (array_key_exists('issuers', $method) && is_array($method['issuers'])) {
                $issuers = [];

                foreach ($method['issuers'] as $issuer) {
                    $issuers[$issuer['issuerId']] = $issuer;
                }

                $method['issuers'] = $issuers;
            }
        }

        return $result;
    }

    /**
     * If an issuer is listed under more than one payment method, then
     * it will appear more than once in the list.
     * However, the list can be restricted using allowedMethods
     *
     * @return Issuer[]
     */
    public function getIssuers()
    {
        $result = [];

        foreach ($this->getPaymentMethodsAssoc() as $paymentMethod) {
            if (! empty($paymentMethod['issuers'])) {
                foreach ($paymentMethod['issuers'] as $issuer) {
                    $result[] = new Issuer(
                        $issuer['issuerId'],
                        $issuer['name'],
                        $paymentMethod['brandCode']
                    );
                }
            }
        }

        return $result;
    }
}
