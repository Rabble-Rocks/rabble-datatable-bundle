<?php

namespace Rabble\DatatableBundle\DependencyInjection\Compiler;

use Rabble\DatatableBundle\ExpressionLanguage\VariableProviderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class ExpressionLanguagePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('rabble.datatable.expression_language');
        $taggedServices = $container->findTaggedServiceIds('rabble_datatable.expression_language.provider');
        foreach ($taggedServices as $id => $tags) {
            $providerDefinition = $container->findDefinition($id);
            if (is_subclass_of($providerDefinition->getClass(), ExpressionFunctionProviderInterface::class)) {
                $definition->addMethodCall('registerProvider', [new Reference($id)]);
            }
            if (is_subclass_of($providerDefinition->getClass(), VariableProviderInterface::class)) {
                $definition->addMethodCall('registerVariableProvider', [new Reference($id)]);
            }
        }
    }
}
