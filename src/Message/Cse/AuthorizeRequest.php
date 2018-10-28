<?php

namespace Omnipay\Adyen\Message\Cse;

/**
 * Authorize a payment.
 */

use InvalidArgumentException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Adyen\Message\Api\AuthorizeRequest as ApiAuthorizeRequest;

class AuthorizeRequest extends ApiAuthorizeRequest
{
    protected $endpointService = 'authorise';

    /**
     * @var string Name of the encrypted data POST parameter.
     */
    protected $encryptedDataName = 'adyen-encrypted-data';

    /**
     * Get the payment data for the additionalData array.
     * This will be the CC data encrypted on the browser client.
     *
     * @return array
     * @throws InvalidRequestException
     */
    public function getPaymentMethodData()
    {
        if ($this->getEncryptedData() === null) {
            throw new InvalidRequestException(sprintf(
                'The encryptedData parameter or %s POST data is required',
                $this->encryptedDataName
            ));
        }

        return [
            'card.encrypted.json' => $this->getEncryptedData(),
        ];
    }

    /**
     * @inherit
     */
    public function setCardToken($value)
    {
        return $this->setEncryptedData($value);
    }

    /**
     * @inherit
     */
    public function getCardToken()
    {
        return $this->getEncryptedData();
    }

    /**
     * Set the encryptedData value if the application sources
     * it itself.
     *
     * @param string $value
     * @return $this
     */
    public function setEncryptedData($value)
    {
        return $this->setParameter('encryptedData', $value);
    }

    /**
     * Get the encryptedData paraemeter either from the value
     * set by the application, or from the current request POST
     * data.
     * This would normally be used when the payment form is being
     * submitted, but may not if the application handles the card
     * and the additional payment details is separate forms.
     *
     * @return string|null
     */
    public function getEncryptedData()
    {
        // If the application has supplied the encrypted data from its
        // own source, then use that in preference.

        $value = $this->getParameter('encryptedData');

        if ($value !== null) {
            return $value;
        }

        // Find it in the current POST data instead.

        return ($this->httpRequest->request->get(
            $this->encryptedDataName
        ));
    }
}
