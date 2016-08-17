<?php

namespace Omnipay\USAePay\Message;

/**
 * USAePay Create Subscription Request
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
 * // Create a credit card object
 * // This card can be used for testing.
 * $card = new CreditCard(array(
 *             'firstName'    => 'Example',
 *             'lastName'     => 'Customer',
 *             'number'       => '4242424242424242',
 *             'expiryMonth'  => '01',
 *             'expiryYear'   => '2020',
 *             'cvv'          => '123',
 * ));
 *
 * // Do a create card transaction on the gateway
 * $transaction = $gateway->createSubscription(array(
 *     'amount'                   => '1.00',
 *     'card'                     => $card,
 *     'addCustomer'              => true,
 *     'interval'                 => 'month',
 *     'intervalCount'            => 3,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "CreateSubscription transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 */
class CreateSubscriptionRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('amount', 'addCustomer', 'interval', 'intervalCount');

        $this->validate('card');
        $this->getCard()->validate();

        return array(
            'amount' => $this->getAmount(),
            'card' => $this->getCard(),
            'addCustomer' => $this->getAddCustomer(),
            'interval' => $this->getInterval(),
            'intervalCount' => $this->getIntervalCount(),
        );
    }

    public function getCommand()
    {
        return 'cc:sale';
    }
}
