<?php

namespace Omnipay\Adyen;

use Omnipay\Tests\GatewayTestCase;

class HppGatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new HppGateway($this->getHttpClient(), $this->getHttpRequest());

        $this->gateway->initialize([
            'merchantAccount' => 'merchantAccount',
            'username' => 'username',
            'password' => 'password',
        ]);
    }

    public function testAuthorize()
    {
        $options = array(
            'amount' => '10.00',
            'card' => $this->getValidCard(),
        );
        $request= $this->gateway->authorize($options);

        $this->assertInstanceOf(\Omnipay\Adyen\Message\Hpp\AuthorizeRequest::class, $request);
        $this->assertArrayHasKey('paymentAmount', $request->getData());
    }

    public function testCompleteAuthorize()
    {
        $options = array(
            'transactionReference' => 'abc123'
        );
        $request = $this->gateway->completeAuthorize($options);

        $this->assertInstanceOf(\Omnipay\Adyen\Message\Hpp\CompleteAuthorizeRequest::class, $request);
        //$this->assertArrayHasKey('transactionReference', $request->getData());
    }

    public function testCapture()
    {
        $options = array(
            'transactionReference' => 'abc123'
        );
        $request = $this->gateway->capture($options);

        $this->assertInstanceOf('\Omnipay\Adyen\Message\Api\CaptureRequest', $request);
        $this->assertArrayHasKey('originalReference', $request->getData());
    }

    public function testRefund()
    {
        $options = array(
            'transactionReference' => 'abc123'
        );
        $request = $this->gateway->refund($options);

        $this->assertInstanceOf('\Omnipay\Adyen\Message\Api\RefundRequest', $request);
        $this->assertArrayHasKey('originalReference', $request->getData());
    }

    public function testVoid()
    {
        $options = array(
            'transactionReference' => 'abc123',
        );
        $request = $this->gateway->void($options);

        $this->assertInstanceOf('\Omnipay\Adyen\Message\Api\CancelRequest', $request);
        $this->assertArrayHasKey('originalReference', $request->getData());
    }
}
