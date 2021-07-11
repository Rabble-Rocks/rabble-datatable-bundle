<?php

namespace Rabble\DatatableBundle\DependencyInjection\Compiler;

use Rabble\DatatableBundle\Datatable\AbstractGenericDatatable;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;

class DatatablePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('rabble.datatable.datatable_collection');
        $taggedServices = $container->findTaggedServiceIds('rabble_datatable');
        $prioritizedServices = [];
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $arguments) {
                $priority = 0;
                if (isset($arguments['priority'])) {
                    $priority = $arguments['priority'];
                    unset($arguments['priority']);
                }
                if (!count($arguments)) {
                    $arguments = null;
                } else {
                    foreach ($arguments as $i => $argument) {
                        if ('@=' == substr($argument, 0, 2)) {
                            $argument = new Expression(substr($argument, 2));
                        } elseif ('@' == substr($argument, 0, 1)) {
                            $argument = new Reference(substr($argument, 1));
                        }
                        $arguments[$i] = $argument;
                    }
                }

                if (!isset($prioritizedServices[$priority])) {
                    $prioritizedServices[$priority] = [];
                }
                $prioritizedServices[$priority][] = ['configuration' => $arguments, 'id' => $id];
            }
        }
        ksort($prioritizedServices);
        foreach ($prioritizedServices as $services) {
            foreach ($services as $service) {
                $datatableDefinition = $container->findDefinition($service['id']);
                $definition->addMethodCall('set', [new Expression("service('".addcslashes($service['id'], '\\')."').getName()"), new Reference($service['id'])]);
                $configuration = $service['configuration'];
                if (is_array($configuration)) {
                    $datatableDefinition->addMethodCall('setConfiguration', [$configuration]);
                }
                if (AbstractGenericDatatable::class == $datatableDefinition->getClass() || is_subclass_of($datatableDefinition->getClass(), AbstractGenericDatatable::class)) {
                    $datatableDefinition->addMethodCall('setExpressionLanguage', [new Reference('rabble.datatable.expression_language')]);
                    $datatableDefinition->addMethodCall('setTemplating', [new Reference('twig')]);
                    if (!$datatableDefinition->hasMethodCall('setEventDispatcher')) {
                        $datatableDefinition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);
                    }
                }
            }
        }
    }
}
