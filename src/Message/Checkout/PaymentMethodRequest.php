<?php

namespace Omnipay\Adyen\Message\Checkout;

use Omnipay\Adyen\Message\AbstractCheckoutRequest;
use Omnipay\Adyen\Message\AbstractRequest;

class PaymentMethodRequest extends AbstractCheckoutRequest
{
    public function createResponse($data)
    {
        return new PaymentMethodResponse($this, $data);
    }

    public function getEndpoint()
    {
        return $this->getCheckoutUrl(
            AbstractRequest::SERVICE_GROUP_PAYMENT_PAYMENTMETHODS
        );
    }

    public function getData()
    {
        $this->validate('merchantAccount');

        $data = [
            'merchantAccount' => $this->getMerchantAccount(),
        ];

        if (!empty($this->getChannel())) {
            $data['channel'] = $this->getChannel();
        }

        if (!empty($this->getCountryCode())) {
            $data['countryCode'] = $this->getCountryCode();
        }

        if (!empty($this->getShopperLocale())) {
            $data['shopperLocale'] = $this->getShopperLocale();
        }

        if (!empty($this->getAmountInteger()) && !empty($this->getCurrency())) {
            $data['amount']['value'] = $this->getAmountInteger();
            $data['amount']['currency'] = $this->getCurrency();
        }

        if (!empty($this->getShopperReference())) {
            $data['shopperReference'] = $this->getShopperReference();
        }

        return $data;
    }
}
