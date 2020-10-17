<?php


namespace Omnipay\Adyen\Message\Checkout;


use Omnipay\Adyen\Traits\DataWalker;
use Omnipay\Common\Message\AbstractResponse;

class CreateCardResponse extends AbstractResponse
{
    use DataWalker;

    public function isSuccessful()
    {
        return (isset($this->data['resultCode']) && $this->data['resultCode'] == 'Authorised');
    }

    public function getToken() {
        return $this->data['additionalData']['recurring.recurringDetailReference'];
    }

}