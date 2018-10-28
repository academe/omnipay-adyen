<?php

namespace Omnipay\Adyen\Message\Hpp;

/**
 * Send the user to the Hosted Payment Page to authorize their payment.
 */

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class AuthorizeResponse extends AbstractResponse implements RedirectResponseInterface
{
    public function getRedirectUrl()
    {
        // If a brandCode has been supplied, then use the "skipDetyails" endpoint.

        if ($brandCode = $this->getBrandCode()) {
            return 'https://test.adyen.com/hpp/skipDetails.shtml';
        } else {
            return 'https://test.adyen.com/hpp/pay.shtml';
        }
    }

    public function getBrandCode()
    {
        return isset($this->getData()['brandCode'])
            ? $this->getData()['brandCode']
            : null;
    }

    /**
     * Get the required redirect method (either GET or POST).
     *
     * @return string
     */
    public function getRedirectMethod()
    {
        return 'POST';
    }

    /**
     * Does the response require a redirect?
     *
     * @return boolean
     */
    public function isRedirect()
    {
        return true;
    }

    /**
     * Gets the redirect form data array, if the redirect method is POST.
     *
     * @return array
     */
    public function getRedirectData()
    {
        return $this->getData();
    }

    public function isSuccessful()
    {
        return false;
    }
}
