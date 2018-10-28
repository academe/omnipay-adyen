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
    protected $testEndpointDirectory = 'https://test.adyen.com/hpp/directory/{version}.shtml';
    protected $liveEndpointDirectory = 'https://live.adyen.com/hpp/directory/{version}.shtml';

    // Terminal services
    protected $testEndpointTerminal = 'https://terminal-api-test.adyen.com';
    protected $liveEndpointTerminal = 'https://terminal-api-live.adyen.com';

    // Checkout Utility
    protected $testEndpointCheckoutUtility = 'https://checkout-test.adyen.com/v1';
    protected $liveEndpointCheckoutUtility = 'https://checkout-live.adyen.com/v1';

    // Checkout services
    protected $testEndpointCheckoutServices = 'https://checkout-test.adyen.com/v32/{service}';
    protected $liveEndpointCheckoutServices = 'https://checkout-live.adyen.com/v32/{service}';

    const VERSION_DIRECTORY = 'v2';
    const VERSION_CHECKOUT = 'v32';
    const VERSION_CHECKOUT_UTILITY = 'v1';
    const VERSION_PAYMENTS = 'v30';

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
    public function getPaymentUrl($service, $group = 'Payment', $version = self::VERSION_PAYMENTS)
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
     * The Directory URL has no parameters.
     */
    public function getDirectoryUrl($version = self::VERSION_DIRECTORY)
    {
        $template = $this->getTestMode()
            ? $this->testEndpointDirectory
            : $this->liveEndpointDirectory;

        return $this->expandUrlTemplate($template, [
            'version' => $version,
        ]);
    }

    /**
     * @return string
     */
    public function getSessionValidity()
    {
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

        list($contentType) = explode(';', $contentType, 2);

        $body = (string)$response->getBody();

        if ($contentType === $this->returnContentType) {
            $payload = json_decode($body, true);

            // TODO: check for JSON errors after parsing.

            return $payload;
        }

        // TODO: if comntent type if "utf8" then the body will contain a
        // text/plain error message that can be captured.

        throw new InvalidRequestException(sprintf(
            'Unexpected content type "%s" and code "%s"; expecting "%s"',
            $contentType,
            $response->getStatusCode(),
            $this->returnContentType
        ));
    }

    /**
     * Generate a signature for a signing string.
     *
     * @param string $signingString
     * @param string $secret
     * @return string
     * @see https://docs.adyen.com/developers/hpp-manual#hpphmaccalculation
     */
    public function generateSignature($signingString, $secret)
    {
        // base64-encode the binary result of the HMAC computation.

        return base64_encode(hash_hmac(
            'sha256',
            $signingString,
            pack("H*", $secret),
            true
        ));
    }
}
