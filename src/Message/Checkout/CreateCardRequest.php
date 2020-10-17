<?php


namespace Omnipay\Adyen\Message\Checkout;


use Omnipay\Adyen\Message\AbstractCheckoutRequest;
use Omnipay\Adyen\Message\AbstractRequest;

class CreateCardRequest extends AbstractCheckoutRequest
{

    public function createResponse($data)
    {
        return new CreateCardResponse($this, $data);
    }

    public function getEndpoint()
    {
        return $this->getCheckoutUrl(
            AbstractRequest::SERVICE_GROUP_PAYMENT_PAYMENTS
        );
    }

    public function getData()
    {
        $this->validate(
            'merchantAccount',
            'paymentMethod',
            'currency',
            'transactionId',
            'shopperReference');

        $data = [
            'amount' => [
                'value' => 0,
                'currency' => $this->getCurrency(),
            ],
            'paymentMethod' => $this->getPaymentMethod(),
            "storePaymentMethod" => true,
            'merchantAccount' => $this->getMerchantAccount(),
            'reference' => $this->getTransactionId(),
            'shopperInteraction' => 'Ecommerce',
            'recurringProcessingModel' => 'CardOnFile',
            "shopperReference" => $this->getShopperReference(),
        ];

        return $data;
    }

    public function setPaymentMethod($paymentMethod) {
        $this->setParameter('paymentMethod', $paymentMethod);
    }

    public function getPaymentMethod() {
        return $this->getParameter('paymentMethod');
    }
}