<?php

use App\Controller\OrderController;
use App\Database;
use App\Listener\OrderEmailListener;
use App\Listener\OrderSmsListener;
use App\Logger;
use App\Mailer\Mailer;
use App\Texter\SmsTexter;
use Symfony\Component\EventDispatcher\EventDispatcher;

require __DIR__ . '/vendor/autoload.php';

$database = new Database();
$mailer = new Mailer();
$smsTexter = new SmsTexter();
$logger = new Logger();
$dispatcher = new EventDispatcher();

$orderEmailListener = new OrderEmailListener($mailer, $logger);
$orderSmsListener = new OrderSmsListener($smsTexter, $logger);

$dispatcher->addListener("order.before_insert", [$orderEmailListener, "sendToStock"]);
$dispatcher->addListener("order.after_insert", [$orderEmailListener, "sendToCustomer"], 10);
$dispatcher->addListener("order.after_insert", [$orderSmsListener, "sendToCustomer"], 20);

$controller = new OrderController($database, $mailer, $smsTexter, $logger, $dispatcher);

if (!empty($_POST)) {
    $controller->handleOrder();
    return;
}

$controller->displayOrderForm();
