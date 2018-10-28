<?php

namespace Omnipay\Adyen\Message\Api;

/**
 * Run tests for both JSON and Form POST notifications.
 */

use Omnipay\Tests\GatewayTestCase;
use Omnipay\Tests\TestCase;

class NotificationRequestTest extends TestCase
{
    /**
     * Testing we can parse a general JSON notification, complete
     * with HMAC signature checks.
     */
    public function testJsonMessage()
    {
        parent::setUp();

        $rawBody = '{"live":"false","notificationItems":[{"NotificationRequestItem":{"additionalData":{"fraudCheck-25-CVCAuthResultCheck":"0","avsResult":"4 AVS not supported for this card type","cardSummary":"1237","shopperCountry":"KR","refusalReasonRaw":"AUTHORISED","eci":"05","totalFraudScore":"50","hmacSignature":"tCMgvuxaWxh4\/9FEZ+hfC9gwdc2DTQnBe1hCLfDNNG4=","acquirerAccountCode":"TestPmmAcquirerAccount","expiryDate":"10\/2020","xid":"ODUyNTQwNzI4OTk5MjY1MAAAAAA=","cavvAlgorithm":"1","cardBin":"421234","fraudCheck--1-Pre-Auth-Risk-Total":"50","threeDAuthenticated":"true","cvcResultRaw":"M","acquirerReference":"7CAQ6GR2TF7","liabilityShift":"true","authCode":"76058","cardHolderName":"User Name","isCardCommercial":"unknown","retry.attempt1.acquirerAccount":"TestPmmAcquirerAccount","threeDOffered":"true","retry.attempt1.acquirer":"TestPmmAcquirer","threeDOfferedResponse":"Y","authorisationMid":"1000","authorisedAmountValue":"1199","issuerCountry":"CA","cvcResult":"1 Matches","cavv":"AQIDBAUGBwgJCgsMDQ4PEBESExQ=","retry.attempt1.responseCode":"Approved","authorisedAmountCurrency":"GBP","threeDAuthenticatedResponse":"Y","avsResultRaw":"4","retry.attempt1.rawResponse":"AUTHORISED","acquirerCode":"TestPmmAcquirer"},"amount":{"currency":"GBP","value":1199},"eventCode":"AUTHORISATION","eventDate":"2018-10-28T13:16:56+01:00","merchantAccountCode":"AcademeComputingLtdUK","merchantReference":"Trans-639224","operations":["CANCEL","CAPTURE","REFUND"],"paymentMethod":"visa","pspReference":"8525407289992650","reason":"76058:1237:10\/2020","success":"true"}}]}';

        $this->getHttpRequest()->initialize(
            [], // GET
            [], // POST
            [], // Attributes
            [], // Cookies
            [], // Files
            [
                'HTTP_CONTENT_TYPE' => 'application/json'
            ], // Server
            $rawBody
        );

        $this->request = new NotificationRequest(
            $this->getHttpClient(),
            $this->getHttpRequest()
        );

        $this->request->setNotificationSecret(
            '26A15FF9B91CBECB01B313816D3D9008B47E810E478D622B4DBDF34D8E085200'
        );

        $this->assertSame('8525407289992650', $this->request->getTransactionReference());
        $this->assertSame('Trans-639224', $this->request->getTransactionId());
        $this->assertSame('completed', $this->request->getTransactionStatus());
        //$this->assertSame([], $this->request->getAdditionalData());
    }
}
