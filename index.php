<?php

use App\Controller\OrderController;
use App\Listener\OrderEmailSubscriber;
use App\Listener\OrderSmsListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;

require __DIR__ . '/vendor/autoload.php';

$container = new ContainerBuilder();

$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
$loader->load("services.yaml");

$container->compile();

/*$database = new Database();
$mailer = new Mailer();
$smsTexter = new SmsTexter();
$logger = new Logger();
$dispatcher = new EventDispatcher();*/

$orderEmailSubscriber = $container->get(OrderEmailSubscriber::class);
$orderSmsListener = $container->get(OrderSmsListener::class);

$dispatcher = $container->get(EventDispatcher::class);

$dispatcher->addListener("order.after_insert", [$orderSmsListener, "sendToCustomer"], 20);
$dispatcher->addSubscriber($orderEmailSubscriber);

$controller = $container->get(OrderController::class);

if (!empty($_POST)) {
    $controller->handleOrder();
    return;
}

$controller->displayOrderForm();
