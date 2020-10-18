<?php

namespace Omnipay\Adyen\Message;

abstract class AbstractCheckoutRequest extends AbstractApiRequest
{

    /**
     * Send the request with specified data.
     *
     * @param array $data The data to send
     * @return Omnipay\Common\Message\ResponseInterface
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        $response = $this->httpClient->request(
            'POST',
            $this->getEndpoint(),
            [
                'Content-Type' => 'application/json',
                // Basic auth header.
                'x-api-key' => $this->getApiKey(),
            ],
            json_encode($data)
        );

        $payload = $this->getJsonData($response);

        return $this->createResponse($payload);
    }

    abstract public function createResponse($payload);
}
