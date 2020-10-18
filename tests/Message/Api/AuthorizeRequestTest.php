<?php

namespace Omnipay\Adyen\Message\Api;

use Omnipay\Tests\GatewayTestCase;
use Omnipay\Tests\TestCase;

class AuthorizeRequestTest extends TestCase
{
    /**
     * Test all the generated URLs in live mode.
     */
    public function testGeneratedLiveUrls()
    {
        parent::setUp();

        $this->request = new AuthorizeRequest(
            $this->getHttpClient(),
            $this->getHttpRequest()
        );

        $this->request->setTestMode(false);

        $request = $this->request;

        // Payment, defaults and all elements set.

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Payment/v64/authorise',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_PAYMENT_AUTHORISE
            )
        );

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Payment/v64/authorise3d',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_PAYMENT_AUTHORISE3D,
                $request::PAYMENT_GROUP_PAYMENT,
                $request::VERSION_PAYMENT_PAYMENT
            )
        );

        // Recurring payment, through payments method and recorring URL helper.

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Recurring/v25/listRecurringDetails',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_RECURRING_LISTRECURRINGDETAILS,
                $request::PAYMENT_GROUP_RECURRING,
                $request::VERSION_PAYMENT_RECURRING
            )
        );

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Recurring/v25/listRecurringDetails',
            $request->getRecurringUrl(
                $request::SERVICE_GROUP_RECURRING_LISTRECURRINGDETAILS
            )
        );

        // Payout payment, through payments method and payout URL helper.

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Payout/v30/submitThirdParty',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_PAYOUT_SUBMITTHIRDPARTY,
                $request::PAYMENT_GROUP_PAYOUT,
                $request::VERSION_PAYMENT_PAYOUT
            )
        );

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Payout/v30/submitThirdParty',
            $request->getPayoutUrl(
                $request::SERVICE_GROUP_PAYOUT_SUBMITTHIRDPARTY
            )
        );

        // CSE Url

        $this->assertSame(
            'https://live.adyen.com/hpp/cse/js/token-token-token.shtml',
            $request->getCseUrl(
                'token-token-token'
            )
        );

        // Directory URL

        $this->assertSame(
            'https://live.adyen.com/hpp/directory/v2.shtml',
            $request->getDirectoryUrl()
        );

        $this->assertSame(
            'https://live.adyen.com/hpp/directory/v99.shtml',
            $request->getDirectoryUrl(
                'v99'
            )
        );
    }

    /**
     * Test all the generated URLs in test mode.
     */
    public function testGeneratedTestUrls()
    {
        parent::setUp();

        $this->request = new AuthorizeRequest(
            $this->getHttpClient(),
            $this->getHttpRequest()
        );

        $this->request->setTestMode(true);

        $request = $this->request;

        // Payment, defaults and all elements set.

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Payment/v64/authorise',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_PAYMENT_AUTHORISE
            )
        );

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Payment/v64/authorise3d',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_PAYMENT_AUTHORISE3D,
                $request::PAYMENT_GROUP_PAYMENT,
                $request::VERSION_PAYMENT_PAYMENT
            )
        );

        // Recurring payment, through payments method and recorring URL helper.

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Recurring/v25/listRecurringDetails',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_RECURRING_LISTRECURRINGDETAILS,
                $request::PAYMENT_GROUP_RECURRING,
                $request::VERSION_PAYMENT_RECURRING
            )
        );

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Recurring/v25/listRecurringDetails',
            $request->getRecurringUrl(
                $request::SERVICE_GROUP_RECURRING_LISTRECURRINGDETAILS
            )
        );

        // Payout payment, through payments method and payout URL helper.

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Payout/v30/submitThirdParty',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_PAYOUT_SUBMITTHIRDPARTY,
                $request::PAYMENT_GROUP_PAYOUT,
                $request::VERSION_PAYMENT_PAYOUT
            )
        );

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Payout/v30/submitThirdParty',
            $request->getPayoutUrl(
                $request::SERVICE_GROUP_PAYOUT_SUBMITTHIRDPARTY
            )
        );

        // CSE Url

        $this->assertSame(
            'https://test.adyen.com/hpp/cse/js/token-token-token.shtml',
            $request->getCseUrl(
                'token-token-token'
            )
        );

        // Directory URL

        $this->assertSame(
            'https://test.adyen.com/hpp/directory/v2.shtml',
            $request->getDirectoryUrl()
        );

        $this->assertSame(
            'https://test.adyen.com/hpp/directory/v99.shtml',
            $request->getDirectoryUrl(
                'v99'
            )
        );
    }
}
