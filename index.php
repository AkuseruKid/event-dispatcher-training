<?php

use App\Controller\OrderController;
use App\DependancyInjection\EventCompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require __DIR__ . '/vendor/autoload.php';

$container = new ContainerBuilder();

$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
$loader->load("services.yaml");

$container->addCompilerPass(new EventCompilerPass());

$container->compile();

$controller = $container->get(OrderController::class);

if (!empty($_POST)) {
    $controller->handleOrder();
    return;
}

$controller->displayOrderForm();
