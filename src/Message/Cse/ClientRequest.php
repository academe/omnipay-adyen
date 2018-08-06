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

class ClientRequest extends AbstractRequest
{
    /**
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
     * Return the URL to the JavaScript library used for
     * encrypting the CC details.
     *
     * @return string URL
     */
    public function getLibraryUrl()
    {
        $this->validate('publicKeyToken');

        return $this->getCseUrl($this->getPublicKeyToken());
    }

    /**
     * Should the get change state? Probably not.
     * Maybe set on instantiation. However, that binds this
     * message probably a little too close to the AbstractRequest.
     *
     * @return string curret datetime.
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
