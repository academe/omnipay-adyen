<?php

namespace Omnipay\Adyen\Message\Hpp;

/**
 *
 */

use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Common\Message\AbstractResponse;

class FetchPaymentMethodsResponse extends AbstractResponse
{
    /**
     * @var array
     */
    protected $paymentMethods = [];

    /**
     * Return the raw payment methods.
     *
     * @param bool $associative Return payment methods as an associative array if set.
     * @return array Array of brandCode/name pairs, or an associative array.
     */
    public function getPaymentMethods($associative = false)
    {
        if (isset($this->data['paymentMethods'])) {
            $data = $this->data['paymentMethods'];

            if ($associative) {
                $array = [];

                foreach ($data as $method) {
                    if (array_key_exists('brandCode', $method) && array_key_exists('name', $method)) {
                        $array[$method['brandCode']] = $method;
                    }
                }

                return $array;
            }
        } else {
            $data = [];
        }

        return $data;
    }

    /**
     * @inherit
     */
    public function isSuccessful()
    {
        return !empty($this->paymentMethods);
    }
}
