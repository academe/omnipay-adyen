<?php

namespace Omnipay\Adyen\Message;

use Omnipay\Common\Message\AbstractRequest as OmnipayAbstractRequest;
use Omnipay\Adyen\Traits\GatewayParameters;
use Omnipay\Common\Exception\InvalidRequestException;

abstract class AbstractRequest extends OmnipayAbstractRequest
{
    use GatewayParameters;

    /**
     * Constants for URL construction.
     */

    const INSTANCE_LIVE = 'live';
    const INSTANCE_TEST = 'test';

    const VERSION_DIRECTORY         = 'v2';
    const VERSION_CHECKOUT          = 'v32';
    const VERSION_CHECKOUT_UTILITY  = 'v1';
    const VERSION_PAYMENT_PAYMENT   = 'v30';
    const VERSION_PAYMENT_RECURRING = 'v25';
    const VERSION_PAYMENT_PAYOUT    = 'v30';

    const PAYMENT_GROUP_PAYMENT     = 'Payment';
    const PAYMENT_GROUP_RECURRING   = 'Recurring';
    const PAYMENT_GROUP_PAYOUT      = 'Payout';

    const SERVICE_GROUP_PAYMENT_AUTHORISE           = 'authorise';
    const SERVICE_GROUP_PAYMENT_AUTHORISE3D         = 'authorise3d';
    const SERVICE_GROUP_PAYMENT_CAPTURE             = 'capture';
    const SERVICE_GROUP_PAYMENT_CANCEL              = 'cancel';
    const SERVICE_GROUP_PAYMENT_REFUND              = 'refund';
    const SERVICE_GROUP_PAYMENT_CANCELORREFUND      = 'cancelOrRefund';
    const SERVICE_GROUP_PAYMENT_VOIDPENDINGREFUND   = 'voidPendingRefund';
    const SERVICE_GROUP_PAYMENT_REFUNDWITHDATA      = 'refundWithData';

    const SERVICE_GROUP_RECURRING_LISTRECURRINGDETAILS      = 'listRecurringDetails';
    const SERVICE_GROUP_RECURRING_DISABLE                   = 'disable';
    const SERVICE_GROUP_RECURRING_TOKENLOOKUP               = 'tokenLookup';
    const SERVICE_GROUP_RECURRING_SCHEDULEACCOUNTUPDATER    = 'scheduleAccountUpdater';

    const SERVICE_GROUP_PAYOUT_STOREDETAIL                      = 'storeDetail';
    const SERVICE_GROUP_PAYOUT_STOREDETAILANDSUBMITTHIRDPARTY   = 'storeDetailAndSubmitThirdParty';
    const SERVICE_GROUP_PAYOUT_SUBMITTHIRDPARTY                 = 'submitThirdParty';
    const SERVICE_GROUP_PAYOUT_CONFIRMTHIRDPARTY                = 'confirmThirdParty';
    const SERVICE_GROUP_PAYOUT_DECLINETHIRDPARTY                = 'declineThirdParty';

    /**
     * URL templates for all the various services.
     * Some of these are not yet implememented by this driver.
     */

    // Pal services.
    protected $urlTemplatePal = 'https://pal-{instance}.adyen.com/pal/servlet/{group}/{version}/{service}';

    // CSE JavaScript library.
    protected $urlTemplateCse = 'https://{instance}.adyen.com/hpp/cse/js/{token}.shtml';

    // Directory services
    // {version} = VERSION_DIRECTORY
    protected $urlTemplateDirectory = 'https://{instance}.adyen.com/hpp/directory/{version}.shtml';

    // Terminal services
    protected $urlTemplateTerminal = 'https://terminal-api-{instance}.adyen.com';

    // Checkout services
    // {version} = VERSION_CHECKOUT
    protected $urlTemplateCheckoutServices = 'https://checkout-{instance}.adyen.com/{version}/{service}';

    // Checkout Utility
    // {version} = VERSION_CHECKOUT_UTILITY
    protected $urlTemplateCheckoutUtility = 'https://checkout-{instance}.adyen.com/{version}';

    protected $returnContentType = 'application/json';

    /**
     * Expand a URL template.
     *
     * @param string $template the URL with {placeholders} in
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
     * Services for Payment group:
     * - authorise
     * - authorise3d
     * - capture
     * - cancel
     * - refund
     * - cancelOrRefund
     * - voidPendingRefund
     * - refundWithData
     *
     * Services for Recurring group:
     * - listRecurringDetails
     * - disable
     * - tokenLookup
     * - scheduleAccountUpdater
     *
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
    public function getPaymentUrl(
        $service,
        $group = self::PAYMENT_GROUP_PAYMENT,
        $version = self::VERSION_PAYMENT_PAYMENT
    ) {
        return $this->expandUrlTemplate($this->urlTemplatePal, [
            'instance' => ($this->getTestMode() ? static::INSTANCE_TEST : static::INSTANCE_LIVE),
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
        return $this->getPaymentUrl(
            $service,
            static::PAYMENT_GROUP_RECURRING,
            static::VERSION_PAYMENT_RECURRING
        );
    }

    /**
     * @param string the service name
     * @return string the appropriate test/live URL
     */
    public function getPayoutUrl($service)
    {
        return $this->getPaymentUrl(
            $service,
            static::PAYMENT_GROUP_PAYOUT,
            static::VERSION_PAYMENT_PAYOUT
        );
    }

    public function getCseUrl($token)
    {
        return $this->expandUrlTemplate($this->urlTemplateCse, [
            'instance' => ($this->getTestMode() ? static::INSTANCE_TEST : static::INSTANCE_LIVE),
            'token' => $token,
        ]);
    }

    /**
     * The Directory URL has no parameters.
     */
    public function getDirectoryUrl($version = self::VERSION_DIRECTORY)
    {
        return $this->expandUrlTemplate($this->urlTemplateDirectory, [
            'instance' => ($this->getTestMode() ? static::INSTANCE_TEST : static::INSTANCE_LIVE),
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

        // TODO: if content type is "utf8" then the body will contain a
        // text/plain error message that can be captured.

        throw new InvalidRequestException(sprintf(
            'Unexpected content type "%s" and code "%s"; expecting "%s"; reason "%s"',
            $contentType,
            $response->getStatusCode(),
            $this->returnContentType,
            $response->getReasonPhrase()
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
