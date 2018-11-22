# Omnipay: Adyen

**Adyen driver (HPP, CSE and API integration) for the Omnipay PHP payment processing library**

[![Build Status](https://travis-ci.org/academe/omnipay-adyen.svg?branch=master)](https://travis-ci.org/academe/omnipay-adyen)
[![Latest Stable Version](https://poser.pugx.org/academe/omnipay-adyen/version.png)](https://packagist.org/packages/academe/omnipay-adyen)
[![Total Downloads](https://poser.pugx.org/academe/omnipay-adyen/d/total.png)](https://packagist.org/packages/academe/omnipay-adyen)

[Omnipay 3.x](https://github.com/thephpleague/omnipay) is a framework agnostic,
multi-gateway payment processing library for PHP 5.6+.

Table of Contents
=================

   * [Omnipay: Adyen](#omnipay-adyen)
   * [Table of Contents](#table-of-contents)
      * [Installation](#installation)
      * [Hosted Payment Pages (HPP)](#hosted-payment-pages-hpp)
         * [Server Fetches Payment Methods](#server-fetches-payment-methods)
         * [Client Fetches Payment Methods](#client-fetches-payment-methods)
         * [HPP Authorises a Payment](#hpp-authorises-a-payment)
            * [Prepare for Redirect](#prepare-for-redirect)
            * [Complete Transaction on Return](#complete-transaction-on-return)
         * [Capture the Authorisation](#capture-the-authorisation)
      * [Client Side Encryption (CSE)](#client-side-encryption-cse)
         * [Building a Form for Encrypting](#building-a-form-for-encrypting)
         * [An Encrypted Card Authorises a Payment](#an-encrypted-card-authorises-a-payment)
         * [3D Secure Response](#3d-secure-response)
      * [Notifications](#notifications)
         * [Notification Server Request (from gateway)](#notification-server-request-from-gateway)
         * [Notification Response (to gateway)](#notification-response-to-gateway)
      * [Support](#support)

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply require `league/omnipay` and `omnipay/dummy` with Composer:

```
composer require academe/omnipay-adyen
```

## Hosted Payment Pages (HPP)

This method hosts the payment pages on the gateway, and the user is
sent to those pages on the gateway to make a payment.

A number of payment methods are supported, and they will vary depending
on the location of the merchant site (known here as local payment methods).
The list may change also depending on the amount being paid and the currency
being used, as well as the country of the merchant site and the end user.

Options for choosing the payment method include:

* Merchant site gets list of what is available and presents them to the
  end user to choose from. This may be filtered if required.
* The merchant site server chooses one payment method and the end user is
  taken directory to that one choice.
* The front end fetches the options and offers the user a choice.

### Server Fetches Payment Methods

```php
$gateway = Omnipay\Omnipay::create('Adyen\Hpp');

$gateway->initialize([
    'secret' => $hmac,
    'skinCode' => $skinCode,
    'merchantAccount' => $merchantAccount,
    'testMode' => true,
    'currency' => 'EUR',
    // Optional; default set in account:
    'countryCode' => 'GB',
]);

$request = $gateway->fetchPaymentMethods([
    'transactionId' => $transactionId,
    'amount' => 9.99,
]);

$response = $request->send();
```

This gives you an array of `PaymentMethod` objects:

```php
$response->getPaymentMethods();

/*
array(7) {
  [0]=>
  object(Omnipay\Common\PaymentMethod)#48 (2) {
    ["id":protected]=>
    string(6) "diners"
    ["name":protected]=>
    string(11) "Diners Club"
  }
  [1]=>
  object(Omnipay\Common\PaymentMethod)#39 (2) {
    ["id":protected]=>
    string(8) "discover"
    ["name":protected]=>
    string(8) "Discover"
  }
  ...
}
*/
```

The raw payment methods, as associatively indexed arrays, can be fetched too.
These include logos in a variety of sizes.

```php
$response->getPaymentMethodsAssoc();

/*
array(7) {
  ["diners"]=>
  array(3) {
    ["brandCode"]=>
    string(6) "diners"
    ["logos"]=>
    array(3) {
      ["normal"]=>
      string(44) "https://test.adyen.com/hpp/img/pm/diners.png"
      ["small"]=>
      string(50) "https://test.adyen.com/hpp/img/pm/diners_small.png"
      ["tiny"]=>
      string(49) "https://test.adyen.com/hpp/img/pm/diners_tiny.png"
    }
    ["name"]=>
    string(11) "Diners Club"
  }
  ...
}
*/
```

Some payment methods will also have a list of `issuers` that may also be
used to refine the options offered to the end user.
At this time, there are no further parsing of this data into objects
by this driver.

### Client Fetches Payment Methods

Use the same method as the previous section (Server Fetches Payment Methods)
but do not `send()` the request.
Instead, get its data and endpoint for use by the client:

```php
$data = $request->getData();
$endpoint = $request->getEndpoint();
```

`POST` the `$data` to the `$endpoint` to get the JSON response.
Rememeber this data is signed, so parameters cannot be changed at
the client side.

### HPP Authorises a Payment

#### Prepare for Redirect

The gateway object is instantiated first, as before.

```php
$gateway = Omnipay\Omnipay::create('Adyen\Hpp');

$gateway->initialize([
    'secret' => $hmac,
    'skinCode' => $skinCode,
    'merchantAccount' => $merchantAccount,
    'testMode' => true,
    'currency' => 'EUR',
    'countryCode' => 'DE',
]);
```

The `CreditCard` class is used to supply any billing details.
Shipping detais are not supported at this time, but may be later.

```php
$card = new Omnipay\Common\CreditCard([
    'firstName' => 'Joe',
    'lastName' => 'Bloggs',

    'billingAddress1' => '88B',
    'billingAddress2' => 'Address 2B',
    'billingState' => 'StateB',
    'billingCity' => 'CityB',
    'billingPostcode' => '412B',
    'billingCountry' => 'GB',
    'billingPhone' => '01234 567 890',

    'email' =>  'jason@example.co.uk',
]);
```

The request sets up the redirect:

```php
$request = $gateway->authorize([
    'transactionId' => $transactionId,
    'amount' => 9.99,
    // The returnUrl can be defined in the account, and overridden here.
    'returnUrl' => 'https://example.co.uk/your/return/endpoint',
    'card' => $card,
]);
```

Now there are a few additional parameters that need some explanation.

The `paymentMethod` can be used to redirect the user to a specific payment method,
without asking the user to choose from the available payment methods:

    $request->setPaymentMethod('visa');
    $request->setIssuer('optional issuer ID for the brandCode');

Specifying the `paymentMethod` will skip any pages asking the user how they
want to pay, and take the user direct to that payment method.

An alternative way to limit the payment methods that are offered to the user,
is to use the `allowedMethods` and `blockedMethods` lists.

    $request->setAllowedMethod(['visa', 'diner']); // Only Visa and Diner cards.
    $request->setBlockesMethod(['visa']); // All types except Visa.

Setting `addressHidden` to `true` will hide the address being submitted to
the payment gateway.
The user will not see their address, but what you submit will be stored at
the gateway.
By defauit, the address is shown to the user.

    $request->setAddressHidden(true);

Setting `addressLocked` will prevent the user from changing their address
details on the gateway.
Although all these details are sent to he gateway in the redirect, they are
signed, so any attempt by the user to change them will result in a rejection.

    $request->setAddressLocked(true);

Additional parameters will be supported in the future.

Now "send" the request to get the redirection response.

    $response = $request->send();

The redirect will be a `POST`.
The details to include are in `$response->getData()` and `$response->getRedirectUrl()`,
so you can build a form to post or auto-post, either to the top page
or to an iframe. Or you can just issue `echo $response->redirect()` as a rough-and-ready
redirection.

The user will be redirected, will enter their authorisation details on the
gateway hosted page, then will be returned to the `returnUrl`.
This is where the transaction is completed.

#### Complete Transaction on Return

The user will be returned with the result of the authorisation as query parameters.
These are read and parsed like this:

    $response = $gateway->completeAuthorize()->send();

From the `$response` you can get the result, the `transactionReference`
(if the result is successful) and the raw data.

```php
var_dump($response->getdata());
var_dump($response->getAuthResult());
var_dump($response->isSuccessful());
var_dump($response->isPending());
var_dump($response->isCancelled());
var_dump($response->getTransactionReference());
var_dump($response->getTransactionId());
```

The data is signed, and if the signature is invalid then an exception will
be thrown during the `send()` operation.

The get further details about the authorisation, the transaction will need to
be fetched from the gateway using the API.
This result just gives you the overall result, and returns your `transactionId`
so you can confirm it is the result of the transaction you are expecting
(it is vital to check the `transactionId`) so URLs from pevious authorisations
cannot be injected by an end user.

### Capture the Authorisation

The HPP reqeust will only authorise a payment.
It still needs to be captured.
Auto-capture *can* be turned on in the account control panel, but it will be
off by default.
It can also be turned on with a delay, so can auto-capture after a set number
of days. 

Programatically requesting a capture of the payment is done like this:

```
$gateway = Omnipay\Omnipay::create('Adyen\Hpp');

$gateway->initialize([
    'merchantAccount' => $merchantAccount,
    'testMode' => true,
    'currency' => 'EUR',
    'username' => $username,
    'password' => $password,
]);

$request = $gateway->capture(
    // The original transaction reference of the authorisation.
    'transactionReference' => $transactionReference,
    // The original amount in full or partial amount.
    'amount' => 9.99,
    // Optionally you can give the request your own reference.
    'transactionId' => $captureTransactionId,
]);
```

The response you get back will return `isSuccessful() === true`
if the request to capture was accepted.
**Note** however that this is just a request to the gateway.
The result of the capture will be returned via the `notificaton`
channel, so at this point you do not know whether the capture will
be successful.

Once it is captured, it can be refunded in total or in part using
the `$gateway->refund([...])` message, taking the same paramers as
`capture`.

Before the authorisation is captured, the authorisation can be
cancelled completely using `$gateway->void([...])`.
The `void` message does not need an `amount`, as it aims to void
the entire authorisation.

Like for `capture`, both `void` and `refund` are just pending results,
with the final result being supplied by a `notification`.

## Client Side Encryption (CSE)

The Adyen gateway allows a credit card form to be used directly in your application page.
The credit card details are not directly submitted to your merchant site,
but are encrypted at the client (browser), and the encrypted string is then submitted to
your site, along with any additional details.

The encrpyted details are then used in place of credit card details when making
an autorisation request to the API, server-to-server.

### Building a Form for Encrypting

The client-side fuctionality can be completely built by hand, but the following
minimal example shows how this library can help build it.
The *laravel blade* view syntax is used in the example.

```php
$gateway = Omnipay\Omnipay::create('Adyen\Cse');

$gateway->initialize([
    'testMode' => true,
    'publicKeyToken' => $cseLibraryPublicKeyToken,
]);

$request = $gateway->encryptionClient([
    'returnUrl' => 'https://merchant-site.example.com/payment-handler',
]);
```

```html
<html>

<head>
    <script type="text/javascript" src="{{ $request->getLibraryUrl() }}"></script>
</head>

<body>
    <form method="POST" action="{{ $request->getReturnUrl() }}" id="adyen-encrypted-form">
        <input type="text" size="20" data-encrypted-name="number" value="4444333322221111" />
        <input type="text" size="20" data-encrypted-name="holderName" value="User Name" />
        <input type="text" size="2" data-encrypted-name="expiryMonth" value="10" />
        <input type="text" size="4" data-encrypted-name="expiryYear" value="2020" />
        <input type="text" size="4" data-encrypted-name="cvc" value="737" />
        <input type="hidden" value="{{ $request->getGenerationtime() }}" data-encrypted-name="generationtime" />
        <input type="submit" value="Pay" />
    </form>

    <script>
    // The form element to encrypt.
    var form = document.getElementById('adyen-encrypted-form');
    // See https://github.com/Adyen/CSE-JS/blob/master/Options.md for details on the options to use.
    var options = {};
    // Bind encryption options to the form.
    adyen.createEncryptedForm(form, options);
    </script>
</body>

</html>
```

Other application-specific fields can be added to the form as needed.
You may also operate this form using AJAX to prevent a full page refresh.
The Adyen CSE documentation provides more details on this.

The `Pay` button will not be enabled until the credit card fields are completed and valid.

The JavaScript library included in the header will then encrypt the card details and
add the result to the hidden `POST` field `adyen-encrypted-data` by default.
You can specify an alternative field name through the options.
This field must be accepted by the `https://example.com/payment-handler` page
(defined as the `returnUrl`) for the next step.

### An Encrypted Card Authorises a Payment

This is the server-side handling.
Once the CSE form has been POSTed to your client site,
the encrypted card details will be available.
It can be used to submit an authorisation like this:

```php
$gateway = Omnipay\Omnipay::create('Adyen\Cse');

$gateway->initialize([
    'merchantAccount' => $merchantAccount,
    'testMode' => true,
    'currency' => 'EUR',
    'countryCode' => 'DE',
    'username' => $username,
    'password' => $password,
]);

$request = $gateway->authorize([
    'amount' => 11.99,
    'transactionId' => $transactionId,
    // The credit card object provides additional billing and
    // shipping details only.
    'card' => $creditCard,
    // You can pass in the encrypted card as the cardToken,
    // or leave the authorize request to extract it from current
    // POST data.
    'cardToken' => $_POST['encryptedData'],
    // If you want to use 3D Secure, then set the 3D Secure flag
    // and the URL to return the user to.
    '3DSecure' => true,
    'returnUrl' => 'https://example.com/complete-3d-secure-handler',
]);

// The response will provide the success status, transaction
// reference, fraud details, limited card details, etc.
$response = $request->send();

if ($response->isSuccessful()) {
    echo $response->getTransactionReference();
} elseif ($response->isRedirect()) {
    // Lazy way to do the redirect.
    // Or use $response->redirectUrl(), redirectMethod(), redirectData()
    $response->redirect();
}
```

If `isSuccessful()` is true, then the transaction is complete and ends here.

If `isSuccessful()` is not true, and `isRedirect()` is true, then the user
has to be redirected to the bank for 3D Secure authorisation.
The `redirectData()` you are given will generally include `PaReq`, `MD` and
`TermUrl`.

### 3D Secure Response

On return from the 3D Secure authorisation, you will need to fetch the
results from the gateway, as that is where they will be held.
The `completeAuthorize()` methods does this, by sending the data it finds
in the `POST` data of the current request.

```php
$request = $gateway->completeAuthorize([
    // shopperIp is not mandatory, but is strongly recommended.
    'shopperIp' => '123.45.67.89',
]);

$response = $request->send();
```

The `$response` will be the final response, as you would get without
the additional 3D Secure step if it was not enabled or available.

## Notifications

The Adyen APIs are by nature asynchronous.
Just about every event can generate a notification to your application.
By default, no notifications are sent, but they can be set up in the
administration pages.

### Notification Server Request (from gateway)

For security, the notifications can use Basic Auth to access your pages,
and a signature based on some key fields provides confirmation that those
fields have not been changed en-route.

Notifications can be sent as SOAP, JSON or Form POST messages.
This driver supports both JSON and Form POST.

The notification request is captured at the notifications endpoint like this:

```php
$gateway = Omnipay\Omnipay::create('Adyen\Api');

$gateway->initialize([
    'testMode' => true,
    // For validating signatures (optional).
    'secret' => $notificationsHmac,
    // For validating Basic Auth (optional).
    'username' => $username,
    'password' => $password,
]);

$request = $gateway->acceptNotification();
```

Note that the notifications HMAC key is not the same as the HPP HMAC key.
The HMAC check is optional, but if you supply the key here,
then the driver will throw an exception if none is supplied by the API.

You may wish to implement Basic Auth through your framework, perhaps in
routing middleware, rather than in this package.
It just depends on how far through your application pipeline you would like
a failing Basic Auth request to go.
That will determine where the details can be logged.

The `$request` will supply a wealth of information regarding the notification,
examples of which include:

```php
// The eventCodel; the type of event.
$request->getEventCode()

// Merchant site transaction ID.
$request->getTransactionId()

// Gateway transaction ID.
$request->getTransactionReference()

// The Auth Code.
$request->getAuthCode()

// The amount requested as a Noney object.
$request->getAmountMoney()

// The captured billing address (returns an array if present).
$request->getBillingAddress()

// Indicates whether this is a live account (not testing).
$request->getLive()

// Test whether the HMAC validation is successful.
$request->isValidHmac()

// Throw an InvalidRequestException exception of the notification
// has an invalid signature.
$request->send()
```

### Notification Response (to gateway)

The gateway will follow an algorithm to retry sending notifications
at increasing intervals, until it gets an acceptance response.
The expected response will vary, depending on what content type the original
notification used.
The http response code must be 200, unless either the signature check or
Basic Auth checks fail.
The `Content-Type` header is *assumed* to be set appropriately.

The response body payload for a `Form` notification must be the text:

    [accepted]

This is shown [in the documenation](https://docs.adyen.com/developers/notifications/set-up-notifications)
as follows, which is wrong (`&5B` vs `%5D`? What content type is this?)
but worth noting in case this documentation needs tweaking:

    &5Baccepted%5D

The response body payload for a `JSON` notification must be the JSON data:

    {"notificationResponse":"[accepted]"}

This driver does not handle the response at this time, so will need
to be coded into the merchant site.
The merchant site must accept the notification, so it is recommended the
message is queued for offline processing so that the notification endpoint
can respond as quickly as possible.

* TODO: programatically implement the response to the gateway.
* TODO: test whether the response must match the request content type,
  or whether it just needs to be consistent with the response `Content-Type`
  header.

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/).
Be sure to add the [omnipay tag](http://stackoverflow.com/questions/tagged/omnipay)
so it can be easily found.

If you believe you have found a bug, please report it using the
[GitHub issue tracker](https://github.com/academe/omnipay-adyen/issues),
or better yet, fork the library and submit a pull request.
