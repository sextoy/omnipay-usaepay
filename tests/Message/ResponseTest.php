<?php

namespace Omnipay\USAePay\Message;

use Omnipay\Tests\TestCase;

class ResponseTest extends TestCase
{
    /**
     * @expectedException Omnipay\Common\Exception\InvalidResponseException
     */
    public function testConstructEmpty()
    {
        $response = new Response($this->getMockRequest(), '');
    }

    /**
     * @group mock
     */
    public function testMockPurchaseSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseSuccess.txt');
        $response = new Response($this->getMockRequest(), $httpResponse->getBody());

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('0', $response->getTransactionReference());
        $this->assertSame('TESTMD', $response->getAuthorizationCode());
        $this->assertEquals('', $response->getMessage());
    }

    /**
     * @group mock
     */
    public function testMockPurchaseFailure()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseFailure.txt');
        $response = new Response($this->getMockRequest(), $httpResponse->getBody());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertStringMatchesFormat('%d', $response->getTransactionReference());
        $this->assertEquals('Card Declined (00)', $response->getMessage());
    }
}
