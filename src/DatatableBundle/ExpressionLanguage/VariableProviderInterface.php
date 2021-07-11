<?php

namespace Rabble\DatatableBundle\ExpressionLanguage;

interface VariableProviderInterface
{
    public function getVariables(): array;
}
