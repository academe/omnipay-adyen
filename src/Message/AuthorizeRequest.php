<?php

namespace Omnipay\Adyen\Message;

/**
 * Authorize a payment.
 * FIXME: this isn't actually an authorise - it's just geneating the HPP form details.
 */

use InvalidArgumentException;

class AuthorizeRequest extends AbstractRequest
{
    /**
     * @var string The path for the message.
     */
    // Both of these work.
    //protected $endpointPath = 'servlet/Payment/authorise';
    //protected $endpointPath = 'servlet/Payment/v30/authorise';
    protected $endpointService = 'authorise';

    /**
     * @var string Name of the encrypted data POST parameter.
     */
    protected $encryptedDataName = 'adyen-encrypted-data';

    protected $liveEndpoint = 'https://pal-live.adyen.com';
    protected $testEndpoint = 'https://pal-test.adyen.com';

    protected $liveRootPath = 'pal';
    protected $testRootPath = 'pal';

    public function getData()
    {
        $this->validate('amount', 'currency', 'merchantAccount', 'transactionId');

        if ($this->getEncryptedData() === null) {
            throw new InvalidRequestException(sprintf(
                'The encryptedData parameter or %s POST data is required',
                $this->encryptedDataName
            ));
        }

        $data = [
            'additionalData' => [
                'card.encrypted.json' => $this->getEncryptedData(),
            ],
            'amount' => [
                'value' => $this->getAmountInteger(),
                'currency' => $this->getCurrency(),
            ],
            'reference' => $this->getTransactionId(),
            'merchantAccount' => $this->getMerchantAccount(),
        ];

        return $data;
    }

    public function createResponse($data)
    {
        return new AuthorizeResponse($this, $data);
    }

    public function setEncryptedData($value)
    {
        return $this->setParameter('encryptedData', $value);
    }

    public function getEncryptedData()
    {
        // If the application has supplied the encrypted data from its
        // own source, then use that in preference.

        $value = $this->getParameter('encryptedData');

        if ($value !== null) {
            return $value;
        }

        // Find it in the current POST data instead.

        return ($this->httpRequest->request->get($this->encryptedDataName));
    }
}
