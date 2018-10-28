<?php

namespace Omnipay\Adyen\Message\Hpp;

/**
 * Complete an HPP Authorize.
 */

use InvalidArgumentException;
use Omnipay\Adyen\Message\AbstractHppRequest;
use Omnipay\Common\Exception\InvalidRequestException;

class CompleteAuthorizeRequest extends AbstractHppRequest
{
    public function getEndPoint($service = null)
    {
        return;
    }

    /**
     * The authorisation result is supplied as query parameters.
     */
    public function getData()
    {
        $data = $this->httpRequest->query->all();

        return $data;
    }

    /**
     * Check that the data has retained its correct signature,
     * before passing it on to the response.
     *
     * @param array $data
     * @return CompleteAuthorizeResponse
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        if (! array_key_exists('merchantSig', $data)) {
            throw new InvalidRequestException(
                'No signature found in server request.'
            );
        }

        $merchantSig = $data['merchantSig'];

        // Remove the signature from the query data.

        unset($data['merchantSig']);

        // Try generating the signature from what's left and we
        // should recover the same signature.

        $signingString = $this->getSigningString($data);
        $generatedSignatureString = $this->generateSignature(
            $signingString,
            $this->getSecret()
        );

        if ($generatedSignatureString !== $merchantSig) {
            throw new InvalidRequestException(
                'Incorrect signature; server request may have been tampered.'
            );
        }

        return new CompleteAuthorizeResponse($this, $data);
    }
}
