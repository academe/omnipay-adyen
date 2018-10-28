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
 */

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Adyen\Message\AbstractRequest;
use Money\Money;
use Money\Currency;
use Omnipay\Adyen\Traits\GatewayParameters;
use Omnipay\Adyen\Traits\DataWalker;
use Omnipay\Common\Exception\InvalidRequestException;

class NotificationRequest extends AbstractRequest implements NotificationInterface
{
    use GatewayParameters;
    use DataWalker;

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
     * The completed/pending/failed status only applies to
     * a subset of the different types of notification that
     * could be received.
     *
     * @inherit
     */
    public function getTransactionStatus()
    {
        $eventCode = $this->getEventCode();
        $success = $this->getSuccess();

        if ($eventCode === static::EVENT_CODE_PENDING) {
            return $success ? static::STATUS_PENDING : static::STATUS_FAILED;
        }

        if ($success === true) {
            return static::STATUS_COMPLETED;
        }

        if ($success === false) {
            return static::STATUS_FAILED;
        }
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
     * @param mixed $value A number, string or boolean
     * @return bool|null
     */
    protected function mapToBoolean($value)
    {
        if (is_string($value)) {
            if (strtolower($value) === 'true' || $value === '1') {
                return true;
            } elseif (strtolower($value) === 'false' || $value === '0') {
                return false;
            }
        }

        if (gettype($value) === 'boolean') {
            return $value;
        }

        if (gettype($value) === 'integer') {
            return (bool)$value;
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
    public function getSuccessRaw()
    {
        return $this->getDataItem(
            'success',
            $this->getDataItem($this->jsonPrefix . '.success')
        );
    }

    /**
     * @return bool|null the success flag
     */
    public function getSuccess()
    {
        return $this->mapToBoolean($this->getSuccessRaw());
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

    /**
     * @return string|null alias for the cardholder name
     */
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
     * The fraud score.
     *
     * @return string|int|null The string will contain an integer result.
     */
    public function getFraudScore()
    {
        return $this->getDataItem(
            'additionalData.totalFraudScore',
            $this->getDataItem($this->jsonPrefix . '.additionalData.totalFraudScore')
        );
    }

    /**
     * Return the fraud check results as an array in the same
     * format as the API authorise response.
     *
     * @return array
     */
    public function getFraudResults()
    {
        $result = [];

        foreach ($this->getAdditionalData() as $key => $accountScore) {
            if (strpos($key, 'fraudCheck-') === 0) {
                if (substr_count($key, '-') === 2) {
                    list(, $checkId, $name) = explode('-', $key);
                    $result[] = [
                        'FraudCheckResult' => [
                            'accountScore' => (int)$accountScore,
                            'checkId' => $checkId,
                            'name' => $name,
                        ],
                    ];
                }
            }
        }

        //fraudCheck

        return $result;
    }

    // TODO: some fraud checking fields:
    // e.g. fraudCheck-21-EmailDomainValidation lots of fields
    // with this general pattern (fraudCheck-N-NameOfCheck)
    // totalFraudScore
    // The API direct result returns JSON data like this:
    //
    //  ["fraudResult"]["results"]=>
    //  array(1) {
    //    ["FraudCheckResult"]=>
    //    array(3) {
    //      ["accountScore"]=>
    //      int(50)
    //      ["checkId"]=>
    //      int(41)
    //      ["name"]=>
    //      string(29) "PaymentDetailNonFraudRefCheck"
    //    }
    //  }
    // to match an additionalData entry like this:
    //  [fraudCheck-41-PaymentDetailNonFraudRefCheck] => 50
    // also ["fraudResult"]["accountScore"] matches [totalFraudScore]
    //
    // TODO: some security fields:
    // threeDAuthenticated threeDOffered (both "true" or "false")
    // threeDOfferedResponse ("Y" or "N", possibly other values)
    // threeDAuthenticatedResponse
    // avsResultRaw
    //
    // Lots more examples here:
    // https://docs.adyen.com/developers/api-reference/payments-api/ \
    // paymentresult/paymentresult-additionaldata

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
     *
     * @return string
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
     * @return bool true if the HAMC signature is valid, or no secret was supplied
     */
    public function isValidHmac()
    {
        if ($this->getSecret() === null) {
            return true;
        }

        $signature = $this->generateSignature($this->getSigningString());

        if ($signature === $this->getHmacSignature()) {
            return true;
        }

        return false;
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
        // This provides a deeper data structure that is easier
        // to walk and provides grouping of related data.

        $this->payload = $this->expandKeys($this->payload);

        return $this;
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
        if (! $this->isValidSignature()) {
            throw new InvalidRequestException(
                'Notification HMAC signature does not match expected signature'
            );
        }

        return $this;
    }

    /**
     * HMAC signing of notifications is optional.
     * The check will only be performed if the appropriate
     * signing key is supplied server side.
     *
     * @return bool
     */
    public function isValidSignature()
    {
        $secret = $this->getNotificationSecret();

        if ($secret === null) {
            // No secret supplied, to we will not be checking the
            // HMAC signature.

            return true;
        }

        // Signature supplied with the server request.

        $requestSignature = $this->getHmacSignature();

        // Signature generated locally.

        $signingData = [
            $this->getPspReference(),
            $this->getOriginalTransactionReference(),
            $this->getMerchantAccountCode(),
            $this->getMerchantReference(),
            $this->getAmountInteger(),
            $this->getCurrencyCode(),
            $this->getEventCode(),
            $this->getSuccessRaw(),
        ];

        $signingString = implode(':', $signingData);

        $generatedSignatureString = $this->generateSignature($signingString, $secret);

        if ($requestSignature !== $generatedSignatureString) {
            throw new InvalidRequestException(
                'Incorrect signature; server request may have been tampered.'
            );
        }

        return true;
    }
}
