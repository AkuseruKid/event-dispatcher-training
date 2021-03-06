<?php

namespace App\Controller;

use App\Database;
use App\Event\OrderEvent;
use App\Model\Order;
use Symfony\Component\EventDispatcher\EventDispatcher;

class OrderController
{

    protected $database;
    protected $dispatcher;

    public function __construct(Database $database, EventDispatcher $dispatcher)
    {
        $this->database = $database;
        $this->dispatcher = $dispatcher;
    }

    public function displayOrderForm()
    {
        require __DIR__ . '/../../views/form.html.php';
    }

    public function handleOrder()
    {
        // Extraction des données du POST et création d'un objet Order
        $order = new Order;
        $order->setProduct($_POST['product'])
            ->setQuantity($_POST['quantity'])
            ->setEmail($_POST['email'])
            ->setPhoneNumber($_POST['phone']);

        $this->dispatcher->dispatch(new OrderEvent($order), "order.before_insert");

        // Enregistrement en base de données :
        $this->database->insertOrder($order);

        $this->dispatcher->dispatch(new OrderEvent($order), "order.after_insert");
    }
}
