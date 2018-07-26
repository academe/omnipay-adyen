<?php

namespace Omnipay\Adyen\Message\Api;

/**
 * Cancel an auathorised payment.
 */

use Omnipay\Adyen\Message\AbstractApiRequest;

class CancelRequest extends AbstractApiRequest
{
    /**
     * TODO: there is also a `technicalCancel` service where the
     * originalMerchantReference (original transactionid) can be supplied
     * instead of the originalPspReferenec (transactionReference).
     */
    public function getEndPoint($service = null)
    {
        $service = ($this->getRefundIfCaptured() ? 'cancelOrRefund' : 'cancel');

        return $this->getPaymentUrl($service);
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

    /**
     * @return mixed
     */
    public function getRefundIfCaptured()
    {
        return $this->getParameter('refundIfCaptured');
    }

    /**
     * If set, then when performing a void, then if the authorisation
     * has already been cleared, a full `refund` will be performed
     * automatically in place of the `cancel`.
     *
     * @param mixed $value Treated as boolean
     * @return $this
     */
    public function setRefundIfCaptured($value)
    {
        return $this->setParameter('refundIfCaptured', $value);
    }
}
