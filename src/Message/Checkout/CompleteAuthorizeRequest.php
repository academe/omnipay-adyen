<?php

namespace Omnipay\Adyen\Message\Checkout;

use Omnipay\Adyen\Message\AbstractCheckoutRequest;
use Omnipay\Adyen\Message\AbstractRequest;

class CompleteAuthorizeRequest extends AbstractCheckoutRequest
{

    public function createResponse($data)
    {
        return new CompleteAuthorizeResponse($this, $data);
    }

    public function getEndpoint()
    {
        return $this->getCheckoutUrl(
            AbstractRequest::SERVICE_GROUP_PAYMENT_PAYMENTS_DETAILS
        );
    }

    public function getData()
    {
        // TODO: for API authorize only, we need username and password set
        // to support Basic Auth needed for the API endpoint.
  
        //$this->validate('details');

        $data = [
           // 'paymentData' => $this->getPaymentData(),
            'details' => [
                'redirectResult' => $_REQUEST['redirectResult']
            ],
        ];

        //$data = $this->addDetailsData($data);

        return $data;
    }

    public function getPaymentData()
    {
        return $this->getParameter('paymentData');
    }

    /**
     * Merge the payment informatino data into the data array.
     * For the API (direct) authorise, the data is merged into
     * the root level, since it is mandatory.
     *
     * @param array $data
     * @return array
     */
    public function addDetailsData(array $data)
    {
        foreach ($this->getDetails() as $detail) {
            $data['details'][$detail['key']] = $this->getRequestParameter()[$detail['key']];
        }

        return $data;
    }

    public function getDetails()
    {
        return $this->getParameter('details');
    }

    public function getRequestParameter()
    {
        return $this->getParameter('requestParameter');
    }

    public function setPaymentData($paymentData)
    {
        $this->setParameter('paymentData', $paymentData);
    }

    public function setRequestParameter($requestParameter)
    {
        $this->setParameter('requestParameter', $requestParameter);
    }

    public function setDetails($details)
    {
        $this->setParameter('details', $details);
    }
}
