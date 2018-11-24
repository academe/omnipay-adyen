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

        // Payment, defaults and all elements set.

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Payment/v30/authorise',
            $this->request->getPaymentUrl(
                $this->request::SERVICE_GROUP_PAYMENT_AUTHORISE
            )
        );

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Payment/v30/authorise3d',
            $this->request->getPaymentUrl(
                $this->request::SERVICE_GROUP_PAYMENT_AUTHORISE3D,
                $this->request::PAYMENT_GROUP_PAYMENT,
                $this->request::VERSION_PAYMENT_PAYMENT
            )
        );

        // Recurring payment, through payments method and recorring URL helper.

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Recurring/v25/listRecurringDetails',
            $this->request->getPaymentUrl(
                $this->request::SERVICE_GROUP_RECURRING_LISTRECURRINGDETAILS,
                $this->request::PAYMENT_GROUP_RECURRING,
                $this->request::VERSION_PAYMENT_RECURRING
            )
        );

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Recurring/v25/listRecurringDetails',
            $this->request->getRecurringUrl(
                $this->request::SERVICE_GROUP_RECURRING_LISTRECURRINGDETAILS
            )
        );

        // Payout payment, through payments method and payout URL helper.

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Payout/v30/submitThirdParty',
            $this->request->getPaymentUrl(
                $this->request::SERVICE_GROUP_PAYOUT_SUBMITTHIRDPARTY,
                $this->request::PAYMENT_GROUP_PAYOUT,
                $this->request::VERSION_PAYMENT_PAYOUT
            )
        );

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Payout/v30/submitThirdParty',
            $this->request->getPayoutUrl(
                $this->request::SERVICE_GROUP_PAYOUT_SUBMITTHIRDPARTY
            )
        );

        // CSE Url

        $this->assertSame(
            'https://live.adyen.com/hpp/cse/js/token-token-token.shtml',
            $this->request->getCseUrl(
                'token-token-token'
            )
        );

        // Directory URL

        $this->assertSame(
            'https://live.adyen.com/hpp/directory/v2.shtml',
            $this->request->getDirectoryUrl()
        );

        $this->assertSame(
            'https://live.adyen.com/hpp/directory/v99.shtml',
            $this->request->getDirectoryUrl(
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

        // Payment, defaults and all elements set.

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Payment/v30/authorise',
            $this->request->getPaymentUrl(
                $this->request::SERVICE_GROUP_PAYMENT_AUTHORISE
            )
        );

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Payment/v30/authorise3d',
            $this->request->getPaymentUrl(
                $this->request::SERVICE_GROUP_PAYMENT_AUTHORISE3D,
                $this->request::PAYMENT_GROUP_PAYMENT,
                $this->request::VERSION_PAYMENT_PAYMENT
            )
        );

        // Recurring payment, through payments method and recorring URL helper.

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Recurring/v25/listRecurringDetails',
            $this->request->getPaymentUrl(
                $this->request::SERVICE_GROUP_RECURRING_LISTRECURRINGDETAILS,
                $this->request::PAYMENT_GROUP_RECURRING,
                $this->request::VERSION_PAYMENT_RECURRING
            )
        );

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Recurring/v25/listRecurringDetails',
            $this->request->getRecurringUrl(
                $this->request::SERVICE_GROUP_RECURRING_LISTRECURRINGDETAILS
            )
        );

        // Payout payment, through payments method and payout URL helper.

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Payout/v30/submitThirdParty',
            $this->request->getPaymentUrl(
                $this->request::SERVICE_GROUP_PAYOUT_SUBMITTHIRDPARTY,
                $this->request::PAYMENT_GROUP_PAYOUT,
                $this->request::VERSION_PAYMENT_PAYOUT
            )
        );

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Payout/v30/submitThirdParty',
            $this->request->getPayoutUrl(
                $this->request::SERVICE_GROUP_PAYOUT_SUBMITTHIRDPARTY
            )
        );

        // CSE Url

        $this->assertSame(
            'https://test.adyen.com/hpp/cse/js/token-token-token.shtml',
            $this->request->getCseUrl(
                'token-token-token'
            )
        );

        // Directory URL

        $this->assertSame(
            'https://test.adyen.com/hpp/directory/v2.shtml',
            $this->request->getDirectoryUrl()
        );

        $this->assertSame(
            'https://test.adyen.com/hpp/directory/v99.shtml',
            $this->request->getDirectoryUrl(
                'v99'
            )
        );
    }
}
