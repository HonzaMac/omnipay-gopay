<?php

require '../vendor/autoload.php';

use Guzzle\Http\Exception\ClientErrorResponseException;
use Omnipay\GoPay\GatewayFactory;

$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

$goId = $_ENV['GO_ID'];
$clientId = $_ENV['CLIENT_ID'];
$clientSecret = $_ENV['CLIENT_SECRET'];

$gateway = GatewayFactory::createInstance($goId, $clientId, $clientSecret, true);

try {
    $orderNo = uniqid();
    $returnUrl = 'http://localhost:8000/gateway-return.php';
    $notifyUrl = 'http://127.0.0.1/online-payments/uuid/notify';
    $description = 'Shopping at myStore.com';

    $goPayOrder = [
        'purchaseData' => [
            'payer'             => [
                'default_payment_instrument' => 'PAYMENT_CARD',
            ],
            'target'            => [
                'type' => 'ACCOUNT',
                'goid' => $goId,
            ],
            'amount'            => 15000,
            'currency'          => 'CZK',
            'order_number'      => $orderNo,
            'order_description' => $description,
            'items'             => [
                ['count' => 1, 'name' => $description, 'amount' => 15000],
            ],
            'callback'          => [
                'return_url' => $returnUrl,
                'notification_url' => $notifyUrl,
            ],
        ],
    ];


    $response = $gateway->purchase($goPayOrder);

    echo 'Our OrderNo: ' . $orderNo . PHP_EOL;
    echo "TransactionId: " . $response->getTransactionId() . PHP_EOL;
    echo "TransactionReference: " . $response->getTransactionReference() . PHP_EOL;
    echo 'Is Successful: ' . (bool) $response->isSuccessful() . PHP_EOL;
    echo 'Is redirect: ' . (bool) $response->isRedirect() . PHP_EOL;

    // Payment init OK, redirect to the payment gateway
    echo $response->getRedirectUrl() . PHP_EOL;
} catch (ClientErrorResponseException $e) {
    dump((string)$e);
    dump($e->getResponse()->getBody(true));
}