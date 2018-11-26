<?php

namespace Omnipay\Adyen\Message\Api;

/**
 * Refund an authorisation.
 */

class RefundRequest extends CaptureRequest
{
    public function getEndpoint()
    {
        return $this->getPaymentUrl(static::SERVICE_GROUP_PAYMENT_REFUND);
    }
}
