<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class OrderEvent extends Event
{
    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }
}