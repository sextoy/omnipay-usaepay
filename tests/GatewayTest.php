<?php

namespace Omnipay\USAePay;

use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());

        $this->options = array(
            'amount' => '10.00',
            'card' => $this->getValidCard(),
        );
    }

    public function testAuthorize()
    {
        $request = $this->gateway->authorize($this->options);

        $this->assertInstanceOf('\Omnipay\USAePay\Message\AuthorizeRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }

    public function testPurchase()
    {
        $request = $this->gateway->purchase($this->options);

        $this->assertInstanceOf('\Omnipay\USAePay\Message\PurchaseRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }
}
