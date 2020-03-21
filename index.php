<?php

use App\Controller\OrderController;

require __DIR__ . '/config/bootstrap.php';

$controller = $container->get(OrderController::class);

if (!empty($_POST)) {
    $controller->handleOrder();
    return;
}

$controller->displayOrderForm();
