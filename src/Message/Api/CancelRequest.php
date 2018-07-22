<?php

namespace Omnipay\Adyen\Message\Api;

/**
 * Cancel an auathorised payment.
 */

use Omnipay\Adyen\Message\AbstractApiRequest;

class CancelRequest extends AbstractApiRequest
{
    public function getEndPoint($service = null)
    {
        return $this->getPaymentUrl('cancel');
    }

    public function getData()
    {
        $data = $this->getBaseData();

        $this->validate('transactionReference');

        $data['originalReference'] = $this->getTransactionReference();

        if ($transactionId = $this->getTransactionId()) {
            $data['reference'] = $transactionId;
        }

        return $data;
    }

    /**
     * @return ModificationResponse
     */
    public function createResponse($payload)
    {
        return new ModificationResponse($this, $payload);
    }
}
