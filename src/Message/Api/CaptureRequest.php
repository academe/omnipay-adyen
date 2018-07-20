<?php

namespace Omnipay\Adyen\Message\Api;

/**
 * Send the user to the Hosted Payment Page to authorize their payment.
 */

use Omnipay\Adyen\Message\AbstractApiRequest;

class CaptureRequest extends AbstractApiRequest
{
    public function getEndPoint($service = null)
    {
        return $this->getPaymentUrl('capture');
    }

    public function getData()
    {
        $data = $this->getBaseData();

        $data['originalReference'] = $this->getTransactionReference();

        if ($transactionId = $this->getTransactionId()) {
            $data['reference'] = $transactionId;
        }

        $data['modificationAmount'] = [
            'currency' => $this->getCurrency(),
            'value' => $this->getAmountInteger(),
        ];

        // Additional data can be supplied here if needed.
        // See this URL (split because of length):
        // https://docs.adyen.com/developers/api-reference/payments-api
        //    /modificationrequest/modificationrequest-additionaldata

        return $data;
    }

    public function createResponse($payload)
    {
        return new CaptureResponse($this, $payload);
    }
}
