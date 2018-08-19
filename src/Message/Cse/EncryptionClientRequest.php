<?php

namespace Omnipay\Adyen\Message\Cse;

/**
 * Client-side support for CSE (Client Side Encryption).
 * This is used to encrypt a the card details on the client,
 * which can then be safely passed to the back-end to process
 * as an authorisation or payment.
 */

use Omnipay\Adyen\Message\AbstractRequest;
use InvalidArgumentException;

class EncryptionClientRequest extends AbstractRequest
{
    /**
     * Use this data to generate the front-end card encryption form.
     * See the documention for details on how to construct the form.
     * The form will submit to the merchant site with the encrypted
     * card data, where a back-end authorisation can then be performed.
     *
     * @inherit
     */
    public function getData()
    {
        $this->validate('publicKeyToken', 'returnUrl');

        $params = [
            'libraryUrl' => $this->getLibraryUrl(),
            'returnUrl' => $this->getReturnUrl(),
            'generationtime' => $this->getGenerationtime(),
        ];

        return $params;
    }

    /**
     * The URL to the JavaScript library used for encrypting the CC details.
     * It will be unique for every site due to the public token it contains.
     *
     * @return string URL
     */
    public function getLibraryUrl()
    {
        $this->validate('publicKeyToken');

        return $this->getCseUrl($this->getPublicKeyToken());
    }

    /**
     * The timestamp the payment form was generated.
     * This will be incorporated into the encrypted CC details, which defines
     * the start of its lifetime. The encrypted CC detail will then need to be
     * used within a limited time.
     * Example: 2017-07-17T13:42:40.428+01:00
     *
     * @return string current datetime; ISO 8601; YYYY-MM-DDThh:mm:ss.sssTZD
     */
    public function getGenerationtime()
    {
        if (! $this->getParameter('generationtime')) {
            $this->setParameter('generationtime', date('c'));
        };

        return $this->getParameter('generationtime');
    }

    /**
     * This is just a service class. There is not really anything
     * to send, so just return the same object in case anyone does
     * "send" this request.
     */
    public function sendData($data)
    {
        return $this;
    }
}
