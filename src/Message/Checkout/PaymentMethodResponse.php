<?php


namespace Omnipay\Adyen\Message\Checkout;


use Omnipay\Adyen\Traits\DataWalker;
use Omnipay\Common\Message\AbstractResponse;

class PaymentMethodResponse extends AbstractResponse
{
    use DataWalker;

    public function isSuccessful()
    {
        return count($this->getData()) > 0;
    }

    public function getPaymentMethodsResponse() {
        return json_encode($this->data);
    }

}