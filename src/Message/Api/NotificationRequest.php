<?php

namespace Omnipay\Adyen\Message\Api;

/**
 * Recieve notifications from the gateway.
 * All notifications are sent as POST server requests, use
 * Basic Auth (optional here, depending on parameters supplied)
 * and optionally will send a HMAC signing string (also optional
 * here).
 *
 * The "Adyen HttpClient 1.0" is supported at this time.
 *
 * TODO: the gateway is expecting a specific payment response, which
 * must be returned.
 */

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Message\AbstractRequest;
use Money\Money;
use Money\Currency;
use Omnipay\Adyen\Traits\GatewayParameters;

class NotificationRequest extends AbstractRequest implements NotificationInterface
{
    use GatewayParameters;

    // Authorisation event.
    const EVENT_CODE_AUTHORISATION = 'AUTHORISATION';

    // Modification Events
    const EVENT_CODE_AUTHORISATION_ADJUSTMENT = 'AUTHORISATION_ADJUSTMENT';
    const EVENT_CODE_CANCELLATION = 'CANCELLATION';
    const EVENT_CODE_REFUND = 'REFUND';
    const EVENT_CODE_CANCEL_OR_REFUND = 'CANCEL_OR_REFUND';
    const EVENT_CODE_CAPTURE = 'CAPTURE';
    const EVENT_CODE_CAPTURE_FAILED = 'CAPTURE_FAILED';
    const EVENT_CODE_REFUND_FAILED = 'REFUND_FAILED';
    const EVENT_CODE_REFUNDED_REVERSED = 'REFUNDED_REVERSED';
    const EVENT_CODE_VOID_PENDING_REFUND = 'VOID_PENDING_REFUND';

    // Recurring events
    const EVENT_CODE_DEACTIVATE_RECURRING = 'DEACTIVATE_RECURRING';
    const EVENT_CODE_RECURRING_CONTRACT = 'RECURRING_CONTRACT';

    // Dispute events
    const EVENT_CODE_CHARGEBACK = 'CHARGEBACK';
    const EVENT_CODE_CHARGEBACK_REVERSED = 'CHARGEBACK_REVERSED';
    const EVENT_CODE_NOTIFICATION_OF_CHARGEBACK = 'NOTIFICATION_OF_CHARGEBACK';
    const EVENT_CODE_NOTIFICATION_OF_FRAUD = 'NOTIFICATION_OF_FRAUD';
    const EVENT_CODE_PREARBITRATION_WON = 'PREARBITRATION_WON';
    const EVENT_CODE_PREARBITRATION_LOST = 'PREARBITRATION_LOST';
    const EVENT_CODE_REQUEST_FOR_INFORMATION = 'REQUEST_FOR_INFORMATION';

    // Manual review events
    const EVENT_CODE_MANUAL_REVIEW_ACCEPT = 'MANUAL_REVIEW_ACCEPT';
    const EVENT_CODE_MANUAL_REVIEW_REJECT = 'MANUAL_REVIEW_REJECT';

    //  Payout events
    const EVENT_CODE_PAYOUT_EXPIRE = 'PAYOUT_EXPIRE';
    const EVENT_CODE_PAYOUT_DECLINE = 'PAYOUT_DECLINE';
    const EVENT_CODE_PAYOUT_THIRDPARTY = 'PAYOUT_THIRDPARTY';
    const EVENT_CODE_REFUND_WITH_DATA = 'REFUND_WITH_DATA';

    // Other
    const EVENT_CODE_HANDLED_EXTERNALLY = 'HANDLED_EXTERNALLY';
    const EVENT_CODE_OFFER_CLOSED = 'OFFER_CLOSED';
    const EVENT_CODE_ORDER_OPENED = 'ORDER_OPENED';
    const EVENT_CODE_ORDER_CLOSED = 'ORDER_CLOSED';
    const EVENT_CODE_PENDING = 'PENDING';
    const EVENT_CODE_PROCESS_RETRY = 'PROCESS_RETRY';
    const EVENT_CODE_REPORT_AVAILABLE = 'REPORT_AVAILABLE';

    // Permitted operations.
    const OPERATIONS_CANCEL = 'CANCEL';
    const OPERATIONS_CAPTURE = 'CAPTURE';
    const OPERATIONS_REFUND = 'REFUND';

    /**
     * The parsed raw payload, either structured JSON or flat POST data.
     */
    protected $payload = [];

    /**
     * The prefix key to get to the notification item in the JSON
     * request payload. Note the array 0 index key; the API may at
     * some point start delivering multiple notifications for each
     * POST. For now, it is exactly one, and when it does happen it
     * is likely to be an an option that needs to be turned on.
     */
    protected $jsonPrefix = 'notificationItems.0.NotificationRequestItem';

    /**
     *
     */
    public function getTransactionStatus()
    {
        // TODO
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->getReason();
    }

    /**
     *
     */
    public function initialize(array $parameters = array())
    {
        parent::initialize($parameters);

        return $this->parseRequest();
    }

    /**
     * @return bool|null true if the live gateway; null if unknown
     */
    public function getLive()
    {
        // 'live' is at the top level, whether JSON or Form POST.

        return $this->mapToBoolean($this->getDataItem('live'));
    }

    /**
     * @return bool|null
     */
    protected function mapToBoolean($value)
    {
        if (is_string($value)) {
            if (strtolower($value) === 'true') {
                return true;
            } elseif (strtolower($value) === 'false') {
                return false;
            }
        }

        if (gettype($value) === 'boolean') {
            return $value;
        }
    }

    /**
     *
     */
    public function getAmountInteger()
    {
        return $this->getDataItem(
            'value',
            $this->getDataItem($this->jsonPrefix . '.amount.value')
        );
    }

    /**
     * This is the currency code sent in the notification.
     * Do not confuse with getCurrency() as supplied by the application.
     * @return string ISO|null currency code
     */
    public function getCurrencyCode()
    {
        return $this->getDataItem(
            'currency',
            $this->getDataItem($this->jsonPrefix . '.amount.currency')
        );
    }

    /**
     * @return Money|null
     */
    public function getAmountMoney()
    {
        $amountInteger = $this->getAmountInteger();
        $currencyCode = $this->getCurrencyCode();

        if ($amountInteger === null || $currencyCode === null) {
            return null;
        }

        return new Money($amountInteger, new Currency($currencyCode));
    }

    /**
     * See documentation on when this is used.
     */
    public function getAuthorisedAmountInteger()
    {
        return $this->getDataItem(
            'additionalData.authorisedAmountValue',
            $this->getDataItem($this->jsonPrefix . '.additionalData.authorisedAmountValue')
        );
    }

    /**
     * See documentation on when this is used.
     * @return string ISO|null currency code
     */
    public function getAuthorisedCurrencyCode()
    {
        return $this->getDataItem(
            'additionalData.authorisedAmountCurrency',
            $this->getDataItem($this->jsonPrefix . '.additionalData.authorisedAmountCurrency')
        );
    }

    /**
     * See documentation on when this is used.
     * @return Money|null
     */
    public function getAuthorisedAmountMoney()
    {
        $amountInteger = $this->getAuthorisedAmountInteger();
        $currencyCode = $this->getAuthorisedCurrencyCode();

        if ($amountInteger === null || $currencyCode === null) {
            return null;
        }

        return new Money($amountInteger, new Currency($currencyCode));
    }

    /**
     * @return string one of static::EVENT_CODE_*
     */
    public function getEventCode()
    {
        return $this->getDataItem(
            'eventCode',
            $this->getDataItem($this->jsonPrefix . '.eventCode')
        );
    }

    /**
     * @return string|null datetime in ISO 8601 format
     */
    public function getEventDate()
    {
        return $this->getDataItem(
            'eventDate',
            $this->getDataItem($this->jsonPrefix . '.eventDate')
        );
    }

    /**
     * @return string|null possibly the merchantId
     */
    public function getMerchantAccountCode()
    {
        return $this->getDataItem(
            'merchantAccountCode',
            $this->getDataItem($this->jsonPrefix . '.merchantAccountCode')
        );
    }

    /**
     * @return string|null
     */
    public function getMerchantReference()
    {
        return $this->getDataItem(
            'merchantReference',
            $this->getDataItem($this->jsonPrefix . '.merchantReference')
        );
    }

    /**
     * @return string|null
     */
    public function getTransactionId()
    {
        return $this->getMerchantReference();
    }

    /**
     * @return string|null
     */
    public function getPspReference()
    {
        return $this->getDataItem(
            'pspReference',
            $this->getDataItem($this->jsonPrefix . '.pspReference')
        );
    }

    /**
     * @return string|null
     */
    public function getTransactionReference()
    {
        return $this->getPspReference();
    }

    /**
     * @return bool|null the raw success flag
     */
    public function getSuccess()
    {
        return $this->mapToBoolean(
            $this->getDataItem(
                'success',
                $this->getDataItem($this->jsonPrefix . '.success')
            )
        );
    }

    /**
     * This is a multi-use field. See the documentation on how to
     * interpret it.
     *
     * @return string|null
     */
    public function getReason()
    {
        return $this->getDataItem(
            'reason',
            $this->getDataItem($this->jsonPrefix . '.reason')
        );
    }

    /**
     * @return array the list of modification operations supported for this transaction
     */
    public function getOperations()
    {
        $operations = $this->getDataItem(
            'operations',
            $this->getDataItem($this->jsonPrefix . '.operations', [])
        );

        if (is_string($operations)) {
            // JSON will be an array, and Form POST will be a CSV string.
            $operations = explode(',', $operations);
        }

        return $operations;
    }

    /**
     * Get additionalData array.
     */
    public function getAdditionalData()
    {
        return $this->getDataItem(
            'additionalData',
            $this->getDataItem($this->jsonPrefix . '.additionalData', [])
        );
    }

    /**
     * Get paymentMethod.
     * @return string|null
     */
    public function getPaymentMethod()
    {
        return $this->getDataItem(
            'paymentMethod',
            $this->getDataItem($this->jsonPrefix . '.paymentMethod')
        );
    }

    /**
     * Get billingAddress.
     * @return string|null
     */
    public function getBillingAddress()
    {
        return $this->getDataItem('billingAddress', null, $this->getAdditionalData());
    }

    /**
     * Get originalReference.
     * @return string|null
     */
    public function getOriginalTransactionReference()
    {
        return $this->getDataItem(
            'originalReference',
            $this->getDataItem($this->jsonPrefix . '.originalReference')
        );
    }

    /**
     * Get raw card expiryDate in MM/YYYY format.
     * @return string|null
     */
    public function getRawExpiryDate()
    {
        return $this->getDataItem(
            'additionalData.expiryDate',
            $this->getDataItem($this->jsonPrefix . '.additionalData.expiryDate')
        );
    }

    /**
     * Get raw card expiry month in MM format.
     * @return string|null
     */
    public function getExpiryMonth()
    {
        $expiryDate = $this->getRawExpiryDate();

        if (strpos($expiryDate, '/') !== false) {
            list($month, $year) = explode('/', $expiryDate);

            return $month;
        }
    }

    /**
     * Get raw card expiry year in YYYY (four digit) format.
     * @return string|null
     */
    public function getExpiryYear()
    {
        $expiryDate = $this->getRawExpiryDate();

        if (strpos($expiryDate, '/') !== false) {
            list($month, $year) = explode('/', $expiryDate);

            return $year;
        }
    }

    /**
     * Get the last four digits of the credit card.
     *
     * @return string|null
     */
    public function getCardSummary()
    {
        return $this->getDataItem(
            'additionalData.cardSummary',
            $this->getDataItem($this->jsonPrefix . '.additionalData.cardSummary')
        );
    }

    public function getNumberLastFour()
    {
        return $this->getCardSummary();
    }

    /**
     * Get the name of the credit card holder.
     *
     * @return string|null
     */
    public function getCardHolderName()
    {
        return $this->getDataItem(
            'additionalData.cardHolderName',
            $this->getDataItem($this->jsonPrefix . '.additionalData.cardHolderName')
        );
    }

    public function getName()
    {
        return $this->getCardHolderName();
    }

    /**
     * Get the authorisation code, when the transaction is authorised
     * successfuly.
     *
     * @return string|null
     */
    public function getAuthCode()
    {
        return $this->getDataItem(
            'additionalData.authCode',
            $this->getDataItem($this->jsonPrefix . '.additionalData.authCode')
        );
    }

    /**
     * The shopper's email address.
     * This field is returned in case of recurring contract notifications.
     *
     * @return string|null
     */
    public function getShopperEmail()
    {
        return $this->getDataItem(
            'additionalData.shopperEmail',
            $this->getDataItem($this->jsonPrefix . '.additionalData.shopperEmail')
        );
    }

    /**
     * The ID that uniquely identifies the shopper.
     * This field is returned in case of recurring contract notifications.
     *
     * @return string|null
     */
    public function getShopperReference()
    {
        return $this->getDataItem(
            'additionalData.shopperReference',
            $this->getDataItem($this->jsonPrefix . '.additionalData.shopperReference')
        );
    }

    /**
     * The supplied HMAC signature.
     *
     * @return string|null
     */
    public function getHmacSignature()
    {
        return $this->getDataItem(
            'additionalData.hmacSignature',
            $this->getDataItem($this->jsonPrefix . '.additionalData.hmacSignature')
        );
    }

    /**
     *
     */
    public function isSuccessful()
    {
        return $this->isSuccess();
    }

    /**
     * The supplied data will be a JSON payload.
     */
    public function getData()
    {
        return $this->payload;
    }

    /**
     * Construct the HMAC string for this notification server request.
     * A specific list of fields are included.
     */
    public function getSigningString()
    {
        $data = [];

        $data['pspReference'] = $this->getPspReference() ?: '';
        $data['originalReference'] = $this->getOriginalTransactionReference() ?: '';
        $data['merchantAccountCode'] = $this->getMerchantAccountCode() ?: '';
        $data['merchantReference'] = $this->getMerchantReference() ?: '';
        $data['value'] = (string)$this->getAmountInteger();
        $data['currency'] = $this->getCurrencyCode() ?: '';
        $data['eventCode'] = $this->getEventCode() ?: '';
        $data['success'] = $this->getSuccess() ? 'true' : 'false';

        // TODO: escape ':' to '\:' and '\' to '\\' in all values
        // before imploding.

        return implode(':', $data);
    }

    /**
     * Generate the HMAC signature from the relavent fields
     * in the notification.
     * Note: this is NOT working at present, and there are no examples
     * of the signatures being used with PHP in any projects that I
     * can find.
     *
     * @return string
     */
    public function generateSignature()
    {
        // base64-encode the binary result of the HMAC computation.

        return base64_encode(hash_hmac(
            'sha256',
            $this->getSigningString(),
            pack("H*", $this->getSecret()), // Equivalent to hex2bin()
            true
        ));
    }

    /**
     * Parse the server request data.
     *
     * @return $this
     */
    protected function parseRequest()
    {
        $contentType = $this->httpRequest->headers->get('Content-Type');

        list($contentType) = explode(';', $contentType);

        // The JSON data is structured, and the form data is flat.

        if ($contentType === 'application/json') {
            $body = (string)$this->httpRequest->getContent();

            $rawData = json_decode($body, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                // We have a valid JSON response.

                $this->payload = $rawData;
            }
        } elseif ($contentType === 'application/x-www-form-urlencoded') {
            // Symfony\Component\HttpFoundation\Request messes with the request keys,
            // so we parse it ourselves.

            $this->payload = $this->parsePostData(
                (string)$this->httpRequest->getContent()
            );
        }

        // Expand keys with dots '.' to arrays.

        $this->payload = $this->expandKeys($this->payload);

        return $this;
    }

    /**
     * Expand any keys using "dot.tokens" to nested child arrays.
     * @param array $arr
     * @return array
     */
    protected function expandKeys(array $arr)
    {
        // See https://stackoverflow.com/questions/51573147

        $result = [];

        foreach($arr as $key => $value) {
            if (is_array($value)) {
                $value = $this->expandKeys($value);
            }

            foreach (array_reverse(explode('.', $key)) as $key) {
                $value = [$key => $value];
            }

            $result = array_merge_recursive($result, $value);
        }

        return $result;


        $result = [];

        while (count($arr)) {
            // Shift the first element off the array - both key and value.
            // We are treating this like a stack of elements to work through,
            // and some new elements may be added to the stack as we go.

            $value = reset($arr);
            $key = key($arr);
            unset($arr[$key]);

            if (strpos($key, '.') !== false) {
                list($base, $ext) = explode('.', $key, 2);

                if (! array_key_exists($base, $arr)) {
                    // This will be another array element on the end of the
                    // arr stack, to recurse into.

                    $arr[$base] = [];
                }

                // Add the value nested one level in.
                // Value at $arr['bar.baz.biz'] is now at $arr['bar']['baz.biz']
                // We may also add to this element before we get to processing it,
                // for example $arr['bar.baz.bam'] to $arr['bar']['baz.bam']
                // which then get further processed to $arr['bar']['baz']['biz', 'bam']

                $arr[$base][$ext] = $value;
            } elseif (is_array($value)) {
                // We already have an array value, so give the value
                // the same treatment in case any keys need expanding further.

                $result[$key] = $this->expandKeys($value);
            } else {
                // A scalar value with no expandable key.

                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Parse a POST payload into an array.
     * This does *not* convert any invalid (for variable names) characters in
     * the keys, as the Symfony request does.
     *
     * @param string $postStr POST payload, e.g. 'foo=bar&foobar'
     * @return array
     */
    public function parsePostData($postStr)
    {
        $data = [];

        $paramPairs = explode('&', $postStr);

        foreach ($paramPairs as $paramPair) {
            if (strpos($paramPair, '=') === false) {
                // A parameter does not need to have a value.
                $data[urldecode($paramPair)] = '';
            } else {
                list($key, $value) = explode('=', $paramPair, 2);

                $data[urldecode($key)] = urldecode($value);
            }
        }

        return $data;
    }

    /**
     * Just return $this as there is no separate response message.
     * A validation check is performed here on the inbound data so
     * we can catch requests that have been fiddled with.
     *
     * HMAC: 578C517AE044B38A352E74525635FD086E39FA2692C00BFF4C7A828E065F9232
     * "hmacSignature for every message in the list
     */
    public function sendData($data)
    {
        // $this->validateServerRequest(); // HMAC, merchantID, 

        return $this;
    }

    /**
     * Get a data item using "dot-notation".
     */
    public function getDataItem($key, $default = null, $target = null)
    {
        if ($target === null) {
            $target = $this->getData();
        }

        if (! is_array($target)) {
            return $default;
        }

        if (is_null($key) || trim($key) == '') {
            return $target;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
                continue;
            }

            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
                continue;
            }

            if ($default instanceof Closure) {
                return $default();
            } else {
                return $default;
            }
        }

        return $target;
    }
}
