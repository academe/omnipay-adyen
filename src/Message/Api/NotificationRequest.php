<?php

namespace Omnipay\Adyen\Message\Api;

/**
 * Recieve notifications from the gateway.
 * All notifications are sent as POST server requests, use
 * Basic Auth (optional here, depending on parameters supplied)
 * and optionally will send a HMAC signing string (also optional
 * here).
 *
 * The "Adyen HttpClient 1.0" is supported at this time.
 *
 * TODO: the gateway is expecting a specific payment response, which
 * must be returned.
 */

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Message\AbstractRequest;

class NotificationRequest extends AbstractRequest implements NotificationInterface
{
    /**
     * Each JSON notification could contain multiple notifications.
     * TODO: support looping over this objct to get these.
     */
    protected $items = [];

    protected $rawData = [];

    protected $live;

    /**
     *
     */
    public function getTransactionReference()
    {
        // TODO
    }

    /**
     *
     */
    public function getTransactionId()
    {
        // TODO
    }

    /**
     *
     */
    public function getTransactionStatus()
    {
        // TODO
    }

    /**
     *
     */
    public function getMessage()
    {
        // TODO
    }

    /**
     * @return bool true if the live gateway
     */
    public function getLive()
    {
        return $this->getData()['live'] ?? null;
    }

    /**
     * @return NotificationResponseItem the first, and likely only, response detail
     */
    public function getFirst()
    {
        $this->getData();

        return count($this->items) ? reset($this->items) : null;
    }

    public function initialize(array $parameters = array())
    {
        parent::initialize($parameters);

        return $this->parseRequest();
    }

    /**
     * The supplied data will be a JSON payload.
     * TODO: Or a HTTP POST can be sent - support them both invisibly.
     * TODO: this parsing should be done in instantiation.
     */
    public function getData()
    {
        return [
            'live' => $this->live,
            'raw_data' => $this->rawData,
            'items' => $this->items,
        ];
    }

    /**
     * Parse the server request and hydrate the object.
     */
    protected function parseRequest()
    {
        $contentType = $this->httpRequest->headers->get('Content-Type');

        list($contentType) = explode(';', $contentType);

        if ($contentType === 'application/json') {
            $body = (string)$this->httpRequest->getContent();

            $rawData = json_decode($body, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                // We have a valid JSON response. Parse it's data out.

                $this->rawData = $rawData;

                $this->live = $rawData['live'];

                if (empty($this->items) && !empty($rawData['notificationItems'])) {
                    foreach ($rawData['notificationItems'] as $notificationItem) {
                        if (!empty($notificationItem['NotificationRequestItem'])) {
                            $responseData = $notificationItem['NotificationRequestItem'];
                            $responseItem = new NotificationResponseItem($this, $responseData);

                            $this->items[] = $responseItem;
                        }
                    }
                }
            }
        } elseif ($contentType === 'application/x-www-form-urlencoded') {
            $rawData = $this->httpRequest->request->all();
        }

        // The JSON data is structured, and the form data is flat.
        // They need to be normalised to a common format.
        // There are some serious inconsistencies that make this difficult.
        // Not least, the JSON format allows multiple notifications to be
        // delivered in a list.

        return $this;
    }

    /**
     * Just return $this as there is no separate response message.
     * A validation check is performed here on the inbound data so
     * we can catch requests that have been fiddled with.
     *
     * HMAC: 578C517AE044B38A352E74525635FD086E39FA2692C00BFF4C7A828E065F9232
     * "hmacSignature for every message in the list
     */
    public function sendData($data)
    {
        // $this->validateServerRequest();

        // Maybe we do return the respone object, with one or more
        // response items in it? The response will be a collection
        // of response items. But where so he handle the difference
        // betweem the inbound JSON and FORM requests?

        return $this;
    }
}
