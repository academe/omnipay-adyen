<?php

namespace Omnipay\Adyen\Message\Hpp;

/**
 *
 */

use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\FetchPaymentMethodsResponseInterface;
use Omnipay\Common\PaymentMethod;

class FetchPaymentMethodsResponse extends AbstractResponse implements FetchPaymentMethodsResponseInterface
{
    /**
     * @var array
     */
    protected $paymentMethods = [];

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
     * @param bool $associative Return payment methods as an associative array if set.
     * @return array List of brandCode/name
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
     * @inherit
     */
    public function isSuccessful()
    {
        return !empty($this->paymentMethods);
    }
}
