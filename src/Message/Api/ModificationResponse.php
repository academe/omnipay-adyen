<?php

namespace Omnipay\Adyen\Message\Api;

/**
 *
 */

use Omnipay\Common\Message\AbstractResponse;

class ModificationResponse extends AbstractResponse
{
    // TODO: Move this to the abstract.

    const RESPONSE_CANCEL_RECEIVED = '[cancel-received]';
    const RESPONSE_CAPTURE_RECEIVED = '[capture-received]';
    const RESPONSE_REFUND_RECEIVED = '[refund-received]';
    const RESPONSE_CANCEL_REFUND_RECEIVED = '[cancelOrRefund-received]';

    /**
     * Successful if no errors were detected.
     * This only indicates that the request for this action
     * was accepted. It is actually pending, and an asynchronous
     * server request notification will contain the actual result.
     */
    public function isSuccessful()
    {
        return $this->getResponseResult() !== null;
    }

    /**
     * This is the reference for the the receipt of the capture request,
     * not the original transaction reference.
     * The final result will be notified via a CAPTURE notification server request.
     */
    public function getTransactionReference()
    {
        return $this->getPspReference();
    }

    // CHECKME: some of these raw data results could be moved to an abstract
    // response if they are shared across multiple responses.

    /**
     * The raw pspReference
     */
    public function getPspReference()
    {
        return $this->getData()['pspReference'] ?? null;
    }

    /**
     * The raw response result.
     */
    public function getResponseResult()
    {
        return $this->getData()['response'] ?? null;
    }

    // The next four are only returned in the event of an error.

    /**
     * The raw response status.
     */
    public function getStatus()
    {
        return $this->getData()['status'] ?? null;
    }

    /**
     * The raw response errorCode.
     */
    public function getErrorCode()
    {
        return $this->getData()['errorCode'] ?? null;
    }

    /**
     * The raw response message.
     */
    public function getMessage()
    {
        return $this->getData()['message'] ?? null;
    }

    /**
     * The raw response validation.
     */
    public function getValidation()
    {
        return $this->getData()['validation'] ?? null;
    }
}
