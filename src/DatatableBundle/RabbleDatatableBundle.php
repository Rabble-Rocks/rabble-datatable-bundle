<?php

namespace Rabble\DatatableBundle;

use Rabble\DatatableBundle\DependencyInjection\Compiler\DatatablePass;
use Rabble\DatatableBundle\DependencyInjection\Compiler\DoctrinePass;
use Rabble\DatatableBundle\DependencyInjection\Compiler\ExpressionLanguagePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RabbleDatatableBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DatatablePass());
        $container->addCompilerPass(new ExpressionLanguagePass());
        $container->addCompilerPass(new DoctrinePass());
    }
}
