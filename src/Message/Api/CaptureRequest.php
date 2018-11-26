<?php

namespace Omnipay\Adyen\Message\Api;

/**
 * Capture an authorisation.
 */

class CaptureRequest extends CancelRequest
{
    public function getEndpoint()
    {
        return $this->getPaymentUrl(static::SERVICE_GROUP_PAYMENT_CAPTURE);
    }

    public function getData()
    {
        $data = parent::getData();

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
}
