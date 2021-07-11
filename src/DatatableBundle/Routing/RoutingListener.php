<?php

namespace Rabble\DatatableBundle\Routing;

use Rabble\AdminBundle\Routing\Event\RoutingEvent;

class RoutingListener
{
    public function onRoutingLoad(RoutingEvent $event)
    {
        $event->addResources('yaml', ['@RabbleDatatableBundle/Resources/config/routing.yml']);
    }
}
