<?php

namespace Omnipay\Adyen\Message\Api;

/**
 * Refund an authorisation.
 */

class RefundRequest extends CaptureRequest
{
    public function getEndPoint($service = null)
    {
        return $this->getPaymentUrl(static::SERVICE_GROUP_PAYMENT_REFUND);
    }
}
