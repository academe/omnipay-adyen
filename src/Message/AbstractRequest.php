<?php

namespace Omnipay\Adyen\Message;

use Omnipay\Common\Message\AbstractRequest as OmnipayAbstractRequest;
use Omnipay\Adyen\Traits\GatewayParameters;
use Omnipay\Common\Exception\InvalidRequestException;

abstract class AbstractRequest extends OmnipayAbstractRequest
{
    use GatewayParameters;

    // Pal services.
    protected $testEndpointPal = 'https://pal-test.adyen.com/pal/servlet/{group}/{version}/{service}';
    protected $liveEndpointPal = 'https://pal-live.adyen.com/pal/servlet/{group}/{version}/{service}';

    // CSE JavaScript library.
    protected $testEndpointCse = 'https://test.adyen.com/hpp/cse/js/{token}.shtml';
    protected $liveEndpointCse = 'https://live.adyen.com/hpp/cse/js/{token}.shtml';

    // Directory services
    protected $testEndpointDirectory = 'https://test.adyen.com/hpp/directory/v2.shtml';
    protected $liveEndpointDirectory = 'https://live.adyen.com/hpp/directory/v2.shtml';

    // Terminal services
    protected $testEndpointTerminal = 'https://terminal-api-test.adyen.com';
    protected $liveEndpointTerminal = 'https://terminal-api-live.adyen.com';

    // Checkout Utility
    protected $testEndpointCheckoutUtility = 'https://checkout-test.adyen.com/v1';
    protected $liveEndpointCheckoutUtility = 'https://checkout-live.adyen.com/v1';

    // Checkout services
    protected $testEndpointCheckoutServices = 'https://checkout-test.adyen.com/v32/{service}';
    protected $liveEndpointCheckoutServices = 'https://checkout-live.adyen.com/v32/{service}';

    const VERSION_CHECKOUT = 'v32';
    const VERSION_CHECKOUT_UTILITY = 'v1';

    protected $returnContentType = 'application/json';

    /**
     * @var string The path for the message.
     */
    protected $endpointPath = '';

    /**
     * Expand a URL template.
     *
     * @param string $templatye the URL with {placeholders} in
     * @param array $parameters keys as placeholder names and values as replacements
     * @return string the expanded URL
     */
    public function expandUrlTemplate($template, array $parameters = [])
    {
        foreach ($parameters as $name => $value) {
            $template = str_replace('{'.$name.'}', $value, $template);
        }

        return $template;
    }

    /**
     * Services for Paymenr group:
     * - authorise
     * - authorise3d
     * - capture
     * - cancel
     * - refund
     * - cancelOrRefund
     * - voidPendingRefund
     * - refundWithData
     * Services for Recurring group:
     * - listRecurringDetails
     * - disable
     * - tokenLookup
     * - scheduleAccountUpdater
     * Services for Payout group:
     * - storeDetail
     * - storeDetailAndSubmitThirdParty   
     * - submitThirdParty
     * - confirmThirdParty
     * - declineThirdParty
     *
     * @param string the service name
     * @return string the appropriate test/live URL
     */
    public function getPaymentUrl($service, $group = 'Payment', $version = 'v30')
    {
        $template = $this->getTestMode()
            ? $this->testEndpointPal
            : $this->liveEndpointPal;

        return $this->expandUrlTemplate($template, [
            'service' => $service,
            'group' => $group,
            'version' => $version,
        ]);
    }

    /**
     * @param string the service name
     * @return string the appropriate test/live URL
     */
    public function getRecurringUrl($service)
    {
        return $this->getPaymentUrl($service, 'Recurring', 'v25');
    }

    /**
     * @param string the service name
     * @return string the appropriate test/live URL
     */
    public function getPayoutUrl($service)
    {
        return $this->getPaymentUrl($service, 'Payout', 'v30');
    }

    public function getCseUrl($token)
    {
        $template = $this->getTestMode()
            ? $this->testEndpointCse
            : $this->liveEndpointCse;

        return $this->expandUrlTemplate($template, [
            'token' => $token,
        ]);
    }

    /**
     * Send the request with specified data.
     * This possibly needs to supply Basic Auth credentials when
     * making any request.
     *
     * @param  array $data The data to send
     * @return Omnipay\Common\Message\ResponseInterface
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        $auth = $this->getUsername() . ':' . $this->getPassword();

        $response = $this->httpClient->request(
            'POST',
            $this->getEndpoint(),
            [
                'Content-Type' => 'application/json',
                // Basic auth header.
                'Authorization' => 'Basic ' . base64_encode($auth)
            ],
            json_encode($data)
        );

        $payload = $this->getJsonData($response);

        return $this->createResponse($payload);
    }

    /**
     * The Diectory URL has no parameters.
     */
    public function getDirectoryUrl()
    {
        $template = $this->getTestMode()
            ? $this->testEndpointDirectory
            : $this->liveEndpointDirectory;

        return $template;
    }

    /**
     * @return string
     */
    public function getSessionValidity() {
        return date('c', time() + (/*$this->getSessionLifetime()*/ 5 * 60));
    }

    /**
     * Return the JSON data from a response, or throw an error.
     */
    protected function getJsonData($response)
    {
        $contentType = $response->getHeaderLine('Content-Type');

        // Response content type is JSON with an encoding indicator,
        // e.g. "application/json;charset=UTF-8" so we need to split
        // that to check the correct return type.

        list($contentType) = explode(';', $contentType);

        if ($contentType === $this->returnContentType) {
            $payload = json_decode((string)$response->getBody(), true);

            // TODO: check for JSON errors after parsing.

            return $payload;
        }

        // TODO: if comntent type if "utf8" then the body will contain a 
        // plain text error message that can be captured.

        throw new InvalidRequestException(sprintf(
            'Unexpected content type "%s" and code "%s"; expecting "%s"',
            $contentType,
            $response->getStatusCode(),
            $this->returnContentType
        ));
    }
}
