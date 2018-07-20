<?php

namespace Omnipay\Adyen\Message\Api;

/**
 *
 */

use Omnipay\Common\Message\AbstractResponse;

class CaptureResponse extends AbstractResponse
{
    // TODO: Move this to the abstract.
    const RESPONSE_RESULT_RECEIVED = '[capture-received]';

    /**
     * Successful if no errors were detected.
     */
    public function isSuccessful()
    {
        // TODO
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

    /*
    example error response (again, shared with a number of responses so could be moved):

    array(4) {
      ["status"]=>
      int(422)
      ["errorCode"]=>
      string(3) "137"
      ["message"]=>
      string(24) "Invalid amount specified"
      ["errorType"]=>
      string(10) "validation"
    }
    */
}
