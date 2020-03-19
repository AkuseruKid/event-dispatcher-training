<?php

use App\Controller\OrderController;
use App\Database;
use App\Logger;
use App\Mailer\Mailer;
use App\Texter\SmsTexter;

require __DIR__ . '/vendor/autoload.php';

$database = new Database();
$mailer = new Mailer();
$smsTexter = new SmsTexter();
$logger = new Logger();

$controller = new OrderController($database, $mailer, $smsTexter, $logger);

if (!empty($_POST)) {
    $controller->handleOrder();
    return;
}

$controller->displayOrderForm();
