# Omnipay: Dummy

**Dummy driver for the Omnipay PHP payment processing library**

[![Build Status](https://travis-ci.org/thephpleague/omnipay-dummy.png?branch=master)](https://travis-ci.org/thephpleague/omnipay-dummy)
[![Latest Stable Version](https://poser.pugx.org/omnipay/dummy/version.png)](https://packagist.org/packages/omnipay/dummy)
[![Total Downloads](https://poser.pugx.org/omnipay/dummy/d/total.png)](https://packagist.org/packages/omnipay/dummy)

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP 5.3+. This package implements Dummy support for Omnipay.

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply require `league/omnipay` and `omnipay/dummy` with Composer:

```
composer require league/omnipay omnipay/dummy
```

## Hosted Payment Pages

This method hosts the payment pages on the gateway, and the user is
sent to those pages to make a payment.

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
    // Optional; default set in account:
    'currency' => 'EUR',
    'countryCode' => 'GB',
]);

$request = $gateway->fetchPaymentMethods([
    'transactionId' => $transactionId,
    'amount' => 9.99,
]);

$response = $request->send();

// Options for $index:
// `false` (default) - the results will be indexed numerically
// `true` - the results will be indexed by `brandeCode`

$response->getPaymentMethods($index)
```

This gives you an array of this this form:

```php
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


### Client Side



## Client Side Encryption (self-hosted payment page)

NOTE: the documentation this is very confusing. It is no clear whether
client-side encryption is a part of hosted payment pages, or not.
Some documentation parts mix them up together, and the endpoints
fot both incolude "hpp" as a part of the path, whil eother documentation
pages recommend to use one method over the other.

The Adyen gateway allows a credit card form to be used in your application page.
The credit card details are not directly submitted to your merchant site,
but are encrypted at the client, and the encrypted string is then submitted to
your site, along with ant additional details.

The encrpyted details are then used in place of credit card details when making
an autorisation request to the API, server-to-server.

The client-side fuctionality can be completely built by hand, but the following
minimal example shows how this library can help build it.
The *laravel blade* view format is used in the example.

```php
$gateway = Omnipay\Omnipay::create('Adyen\Payment');

$gateway->initialize([
    'testMode' => true,
    'publicKeyToken' => $cseLibraryPublicKeyToken,
]);

$request = $gateway->encryptionClient([
    'returnUrl' => 'https://example.com/payment-handler',
]);

```

```html
<html>

<head>
    <script type="text/javascript" src="{{ $request->getLibraryUrl() }}"></script>
</head>

<body>
    <form method="POST" action="<?php echo $response->getReturnUrl(); ?>" id="adyen-encrypted-form">
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

The `Pay` button will not be enabled until the credit card fields are completed and valid.

The JavaScript library included in the header will then encrypt the card details and
add the result to the hidden `POST` field `adyen-encrypted-data` by default.
You can specify an alternative field name through the options.
This field must be accepted by the `https://example.com/payment-handler` page for the
next step.

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/thephpleague/omnipay-dummy/issues),
or better yet, fork the library and submit a pull request.
