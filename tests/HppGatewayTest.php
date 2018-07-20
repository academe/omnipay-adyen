<?php

namespace Omnipay\Adyen;

use Omnipay\Tests\GatewayTestCase;

class HppGatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new HppGateway($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testAuthorize()
    {
        $options = array(
            'amount' => '10.00',
            'card' => $this->getValidCard(),
        );
        $request= $this->gateway->authorize($options);

        $this->assertInstanceOf(\Omnipay\Adyen\Message\Hpp\AuthorizeRequest::class, $request);
        $this->assertArrayHasKey('amount', $request->getData());
    }

    public function testPurchase()
    {
        $options = array(
            'amount' => '10.00',
            'card' => $this->getValidCard(),
        );
        $request= $this->gateway->purchase($options);

        $this->assertInstanceOf(\Omnipay\Adyen\Message\Hpp\PurchaseRequest::class, $request);
        $this->assertArrayHasKey('amount', $request->getData());
    }

    public function testCompleteAuthorize()
    {
        $options = array(
            'transactionReference' => 'abc123'
        );
        $request= $this->gateway->completeAuthorize($options);

        $this->assertInstanceOf(\Omnipay\Adyen\Message\Hpp\CompleteAuthorizeRequest::class, $request);
        $this->assertArrayHasKey('transactionReference', $request->getData());
    }

    public function testCapture()
    {
        $options = array(
            'transactionReference' => 'abc123'
        );
        $request= $this->gateway->capture($options);

        $this->assertInstanceOf('\Omnipay\Adyen\Message\TransactionReferenceRequest', $request);
        $this->assertArrayHasKey('transactionReference', $request->getData());
    }

    public function testCompletePurchase()
    {
        $options = array(
            'transactionReference' => 'abc123'
        );
        $request= $this->gateway->completePurchase($options);

        $this->assertInstanceOf('\Omnipay\Adyen\Message\TransactionReferenceRequest', $request);
        $this->assertArrayHasKey('transactionReference', $request->getData());
    }

    public function testRefund()
    {
        $options = array(
            'transactionReference' => 'abc123'
        );
        $request= $this->gateway->refund($options);

        $this->assertInstanceOf('\Omnipay\Adyen\Message\TransactionReferenceRequest', $request);
        $this->assertArrayHasKey('transactionReference', $request->getData());
    }

    public function testVoid()
    {
        $options = array(
            'transactionReference' => 'abc123'
        );
        $request= $this->gateway->void($options);

        $this->assertInstanceOf('\Omnipay\Adyen\Message\TransactionReferenceRequest', $request);
        $this->assertArrayHasKey('transactionReference', $request->getData());
    }

    public function testCreateCard()
    {
        $options = array(
            'amount' => '10.00',
            'card' => $this->getValidCard(),
        );
        $request= $this->gateway->createCard($options);

        $this->assertInstanceOf('\Omnipay\Adyen\Message\CreditCardRequest', $request);
        $this->assertArrayHasKey('amount', $request->getData());
    }

    public function testUpdateCard()
    {
        $options = array(
            'cardReference' => 'abc123'
        );
        $request= $this->gateway->updateCard($options);

        $this->assertInstanceOf('\Omnipay\Adyen\Message\CardReferenceRequest', $request);
        $this->assertArrayHasKey('cardReference', $request->getData());
    }

    public function testDeleteCard()
    {
        $options = array(
            'cardReference' => 'abc123'
        );
        $request= $this->gateway->deleteCard($options);

        $this->assertInstanceOf('\Omnipay\Adyen\Message\CardReferenceRequest', $request);
        $this->assertArrayHasKey('cardReference', $request->getData());
    }
}
