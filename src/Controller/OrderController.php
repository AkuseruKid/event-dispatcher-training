<?php

namespace App\Controller;

use App\Database;
use App\Event\OrderEvent;
use App\Logger;
use App\Mailer\Email;
use App\Mailer\Mailer;
use App\Model\Order;
use App\Texter\Sms;
use App\Texter\SmsTexter;
use Symfony\Component\EventDispatcher\EventDispatcher;

class OrderController
{

    protected $database;
    protected $mailer;
    protected $texter;
    protected $logger;
    protected $dispatcher;

    public function __construct(Database $database, Mailer $mailer, SmsTexter $texter, Logger $logger, EventDispatcher $dispatcher)
    {
        $this->database = $database;
        $this->mailer = $mailer;
        $this->texter = $texter;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function displayOrderForm()
    {
        require __DIR__ . '/../../views/form.html.php';
    }

    public function handleOrder()
    {
        // Extraction des données du POST et création d'un objet Order (voir src/Model/Order.php)
        $order = new Order;
        $order->setProduct($_POST['product'])
            ->setQuantity($_POST['quantity'])
            ->setEmail($_POST['email'])
            ->setPhoneNumber($_POST['phone']);

        $this->dispatcher->dispatch(new OrderEvent($order), "order.before_insert");

        // Enregistrement en base de données :
        // voir src/Database.php
        $this->database->insertOrder($order);

        // Après enregistrement, on veut envoyer un email au client :
        // voir src/Mailer/Email.php et src/Mailer/Mailer.php
        $email = new Email();
        $email->setSubject("Commande confirmée")
            ->setBody("Merci pour votre commande de {$order->getQuantity()} {$order->getProduct()} !")
            ->setFrom("web@maboutique.com")
            ->setTo($order->getEmail());

        $this->mailer->send($email);

        // Après email au client, on veut logger ce qui se passe :
        // voir src/Logger.php
        $this->logger->log("Email de confirmation envoyé à {$order->getEmail()} !");

        // Après enregistrement on veut aussi envoyer un SMS au client
        // voir src/Texter/Sms.php et /src/Texter/SmsTexter.php
        $sms = new Sms();
        $sms->setNumber($order->getPhoneNumber())
            ->setText("Merci pour votre commande de {$order->getQuantity()} {$order->getProduct()} !");
        $this->texter->send($sms);

        // Après SMS au client, on veut logger ce qui se passe :
        // voir src/Logger.php
        $this->logger->log("SMS de confirmation envoyé à {$order->getPhoneNumber()} !");
    }
}
