<?php

namespace Omnipay\USAePay\Message;

/**
 * USAePay Void Request
 *
 * ### Example
 *
 * <code>
 * // Create a gateway for the USAePay Gateway
 * // (routes to GatewayFactory::create)
 * $gateway = Omnipay::create('USAePay');
 *
 * // Initialise the gateway
 * $gateway->initialize(array(
 *     'testMode' => true
 * ));
 *
 * // Do a void transaction on the gateway
 * $transaction = $gateway->void(array(
 *     'transactionReference'     => '213123213',
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Void transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 */
class VoidRequest extends AuthorizeRequest
{
    public function getData()
    {
        $this->validate('transactionReference');

        return array(
            'amount' => $this->getAmount(),
            'transactionReference' => $this->getTransactionReference(),
        );
    }

    public function getCommand()
    {
        return 'void';
    }
}
