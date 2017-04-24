<?php

namespace Omnipay\USAePay\Message;

use Exception;
use Guzzle;
use Guzzle\Common\Event;
use Omnipay\Common\Message\AbstractRequest as OmnipayAbstractRequest;
use umTransaction;

/**
 * USAePay Abstract Request.
 *
 * This is the parent class for all Stripe requests.
 *
 * @see \Omnipay\Stripe\Gateway
 * @link https://wiki.usaepay.com/
 *
 * @method \Omnipay\USAePay\Message\Response send()
 */
abstract class AbstractRequest extends OmnipayAbstractRequest
{
    protected $liveEndpoint = 'https://www.usaepay.com/gate';

    protected $sandboxEndpoint = 'https://sandbox.usaepay.com/gate';

    protected $intervals = [
        '' => 'disabled',
        'day' => 'daily',
        'week' => 'weekly',
        'month' => 'monthly',
        'year' => 'annually',
    ];

    abstract public function getCommand();

    abstract public function getData();

    public function getSandbox()
    {
        return $this->getParameter('sandbox');
    }

    public function setSandbox($value)
    {
        return $this->setParameter('sandbox', $value);
    }

    public function getSource()
    {
        return $this->getParameter('source');
    }

    public function setSource($value)
    {
        return $this->setParameter('source', $value);
    }

    public function getPin()
    {
        return $this->getParameter('pin');
    }

    public function setPin($value)
    {
        return $this->setParameter('pin', $value);
    }

    public function getInvoice()
    {
        return $this->getParameter('invoice');
    }

    public function setInvoice($value)
    {
        return $this->setParameter('invoice', $value);
    }

    public function getDescription()
    {
        return $this->getParameter('description');
    }

    public function setDescription($value)
    {
        return $this->setParameter('description', $value);
    }

    public function getAddCustomer()
    {
        if ($this->getParameter('addCustomer') === true) {
            return 'yes';
        }

        return '';
    }

    public function setAddCustomer($value)
    {
        return $this->setParameter('addCustomer', $value);
    }

    public function getInterval()
    {
        $interval = $this->getParameter('interval');

        return $this->intervals[$interval];
    }

    public function setInterval($value)
    {
        if (empty($value)) {
            $value = '';
        }

        if (!in_array($value, array_keys($this->intervals))) {
            throw new Exception('Interval not in list of allowed values.');
        }

        return $this->setParameter('interval', $value);
    }

    public function getIntervalCount()
    {
        return $this->getParameter('intervalCount');
    }

    public function setIntervalCount($value)
    {
        return $this->setParameter('intervalCount', (int) $value);
    }

    /**
     * Get HTTP Method.
     *
     * This is nearly always POST but can be over-ridden in sub classes.
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return 'POST';
    }

    public function sendData($data)
    {
        // check if we are mocking a request
        $mock = false;

        $listeners = $this->httpClient->getEventDispatcher()->getListeners('request.before_send');
        foreach ($listeners as $listener) {
            if (get_class($listener[0]) === 'Guzzle\Plugin\Mock\MockPlugin') {
                $mock = true;

                break;
            }
        }

        // if we are mocking, use guzzle, otherwise use umTransaction
        if ($mock) {
            $httpRequest = $this->httpClient->createRequest(
                $this->getHttpMethod(),
                $this->getEndpoint(),
                null,
                $data
            );

            $httpResponse = $httpRequest->send();
        } else {
            $umTransaction = new umTransaction();
            $umTransaction->usesandbox = $this->getSandbox();
            $umTransaction->testmode = $this->getTestMode();
            $umTransaction->key = $this->getSource();
            $umTransaction->pin = $this->getPin();
            $umTransaction->command = $this->getCommand();
            $umTransaction->invoice = $this->getInvoice();
            $umTransaction->amount = $data['amount'];
            $umTransaction->description = $this->getDescription();
            $umTransaction->addcustomer = $this->getAddCustomer();
            $umTransaction->schedule = $this->getInterval();
            $umTransaction->numleft = $this->getIntervalCount();
            $umTransaction->start = 'next';

            if (isset($data['card'])) {
                $umTransaction->card = $this->getCard()->getNumber();
                $umTransaction->exp = $this->getCard()->getExpiryDate('my');
                $umTransaction->cvv2 = $this->getCard()->getCvv();
                $umTransaction->cardholder = $this->getCard()->getName();
                $umTransaction->street = $this->getCard()->getAddress1();
                $umTransaction->zip = $this->getCard()->getPostcode();
                $umTransaction->email = $this->getCard()->getEmail();

                $umTransaction->billfname = $this->getCard()->getBillingFirstName();
                $umTransaction->billlname = $this->getCard()->getBillingLastName();
                $umTransaction->billcompany = $this->getCard()->getBillingCompany();
                $umTransaction->billstreet = $this->getCard()->getBillingAddress1();
                $umTransaction->billstreet2 = $this->getCard()->getBillingAddress2();
                $umTransaction->billcity = $this->getCard()->getBillingCity();
                $umTransaction->billstate = $this->getCard()->getBillingState();
                $umTransaction->billzip = $this->getCard()->getBillingPostcode();
                $umTransaction->billcountry = $this->getCard()->getBillingCountry();
                $umTransaction->billphone = $this->getCard()->getBillingPhone();
            } elseif ($this->getCardReference()) {
                $umTransaction->card = $this->getCardReference();
                $umTransaction->exp = '0000';
            } else {
                $umTransaction->refnum = $this->getTransactionReference();
            }

            $processResult = $umTransaction->Process();

            if ($processResult !== true) {
                throw new Exception($umTransaction->error);
            }

            $httpResponse = Guzzle\Http\Message\Response::fromMessage($umTransaction->rawresult);
        }

        return $this->response = new Response($this, $httpResponse->getBody());
    }

    protected function getEndpoint()
    {
        return $this->getTestMode() ? $this->sandboxEndpoint : $this->liveEndpoint;
    }
}
