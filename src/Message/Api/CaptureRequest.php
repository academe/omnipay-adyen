<?php

namespace Omnipay\Adyen\Message\Api;

/**
 * Capture an authorisation.
 */

class CaptureRequest extends CancelRequest
{
    public function getEndPoint($service = null)
    {
        return $this->getPaymentUrl('capture');
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
