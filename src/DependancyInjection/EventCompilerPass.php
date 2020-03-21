<?php

namespace App\DependancyInjection;

use App\Listener\OrderEmailSubscriber;
use App\Listener\OrderSmsListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $subscribersIds = $container->findTaggedServiceIds('app.event_subscriber');

        $listenersIds = $container->findTaggedServiceIds('app.event_listener');

        var_dump($listenersIds);
        $dispatcherDefinition = $container->findDefinition(EventDispatcher::class);

        foreach($subscribersIds as $id => $data){
            $dispatcherDefinition->addMethodCall('addSubscriber', [
                new Reference(OrderEmailSubscriber::class)
            ]);
        }

        foreach ($listenersIds as $id => $data){
            foreach($data as $tagdata){
                $dispatcherDefinition->addMethodCall('addListener', [
                    $tagdata['event'],
                    [new Reference(OrderSmsListener::class), $tagdata['method']],
                    $tagdata['priority']
                ]);
            }
        }
    }
}