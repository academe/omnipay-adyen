<?php

namespace Omnipay\Adyen\Traits;

trait GatewayParameters
{
    /**
     * @return string|null
     */
    public function getMerchantAccount()
    {
        return $this->getParameter('merchantAccount');
    }

    public function setMerchantAccount($value)
    {
        return $this->setParameter('merchantAccount', $value);
    }

    /**
     * @return string|null
     */
    public function getSkinCode()
    {
        return $this->getParameter('skinCode');
    }

    public function setSkinCode($value)
    {
        return $this->setParameter('skinCode', $value);
    }

    /**
     * @return string|null
     */
    public function getSecret()
    {
        return $this->getParameter('secret');
    }

    public function setSecret($value)
    {
        return $this->setParameter('secret', $value);
    }

    /**
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->getParameter('countryCode');
    }

    public function setCountryCode($value)
    {
        return $this->setParameter('countryCode', $value);
    }

    /**
     * @return string|null
     */
    public function getShopperLocale()
    {
        return $this->getParameter('shopperLocale');
    }

    public function setShopperLocale($value)
    {
        return $this->setParameter('shopperLocale', $value);
    }

    /**
     * @return string|null
     */
    public function getPublicKeyToken()
    {
        return $this->getParameter('publicKeyToken');
    }

    public function setPublicKeyToken($value)
    {
        return $this->setParameter('publicKeyToken', $value);
    }

    /**
     * @return string|null
     */
    public function getUsername()
    {
        return $this->getParameter('username');
    }

    public function setUsername($value)
    {
        return $this->setParameter('username', $value);
    }

    /**
     * @return string|null
     */
    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    /**
     * @return string|null Any value that will be cast to bool in use.
     */
    public function get3DSecure()
    {
        return $this->getParameter('3DSecure');
    }

    public function set3DSecure($value)
    {
        return $this->setParameter('3DSecure', $value);
    }
}
