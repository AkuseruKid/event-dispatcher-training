<?php

namespace App\Listener;

use App\Event\OrderEvent;
use App\Logger;
use App\Mailer\Email;
use App\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderEmailSubscriber implements EventSubscriberInterface
{
    protected $mailer;
    protected $logger;

    public function __construct(Mailer $mailer, Logger $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        /*['eventName' => 'methodName']
        ['eventName' => ['methodName', $priority]]
        ['eventName' => [['methodName1', $priority], ['methodName2']]]*/
        return [
            "order.before_insert" => ["sendToStock"],
            "order.after_insert" => ["sendToCustomer", 50]
        ];
    }

    public function sendToStock(OrderEvent $event)
    {
        $order = $event->getOrder();

        // Avant d'enregistrer, on veut envoyer un email à l'administrateur :
        // voir src/Mailer/Email.php et src/Mailer/Mailer.php
        $email = new Email();
        $email->setSubject("Commande en cours")
            ->setBody("Merci de vérifier le stock pour le produit {$order->getProduct()} et la quantité {$order->getQuantity()} !")
            ->setTo("stock@maboutique.com")
            ->setFrom("web@maboutique.com");

        $this->mailer->send($email);

        // Avant d'enregistrer, on veut logger ce qui se passe :
        // voir src/Logger.php
        $this->logger->log("Commande en cours pour {$order->getQuantity()} {$order->getProduct()}");
    }

    public function sendToCustomer(OrderEvent $event)
    {
        //$event->stopPropagation();
        $order = $event->getOrder();
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
    }

}