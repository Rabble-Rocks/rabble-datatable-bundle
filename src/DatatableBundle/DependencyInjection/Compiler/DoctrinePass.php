<?php

namespace Rabble\DatatableBundle\DependencyInjection\Compiler;

use Rabble\DatatableBundle\Doctrine\DQL\AsString;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds the Doctrine functions.
 */
class DoctrinePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->findDefinition('doctrine.orm.configuration')->addMethodCall('addCustomStringFunction', ['AS_STRING', AsString::class]);
    }
}
