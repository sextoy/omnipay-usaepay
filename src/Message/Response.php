<?php

namespace Omnipay\USAePay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Exception\InvalidResponseException;

/**
 * USAePay Response
 *
 * This is the response class for all USAePay requests.
 *
 * @see \Omnipay\USAePay\Gateway
 */
class Response extends AbstractResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;

        if (empty($data)) {
            throw new InvalidResponseException;
        }

        $this->data = $this->decodeData($data);
    }

    public function decodeData($data)
    {
        $output = array();

        parse_str($data, $output);

        return $output;
    }

    public function isSuccessful()
    {
        return isset($this->data['UMstatus']) && $this->data['UMstatus'] === 'Approved';
    }

    public function getAuthorizationCode()
    {
        return isset($this->data['UMauthCode']) ? $this->data['UMauthCode'] : null;
    }

    public function getTransactionId()
    {
        return isset($this->data['UMrefNum']) ? $this->data['UMrefNum'] : null;
    }

    public function getTransactionReference()
    {
        return isset($this->data['UMrefNum']) ? $this->data['UMrefNum'] : null;
    }

    public function getMessage()
    {
        return isset($this->data['UMerror']) ? $this->data['UMerror'] : null;
    }

    public function getCardReferenceToken()
    {
        return isset($this->data['UMcardRef']) ? $this->data['UMcardRef'] : null;
    }

    public function getCardType()
    {
        return isset($this->data['UMcardType']) ? $this->data['UMcardType'] : null;
    }

    public function getMaskedCardNumber()
    {
        return isset($this->data['UMmaskedCardNum']) ? $this->data['UMmaskedCardNum'] : null;
    }

    public function getCustomerReference()
    {
        return isset($this->data['UMcustnum']) ? $this->data['UMcustnum'] : null;
    }
}
