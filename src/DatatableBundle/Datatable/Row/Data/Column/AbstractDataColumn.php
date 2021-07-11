<?php

namespace Rabble\DatatableBundle\Datatable\Row\Data\Column;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

abstract class AbstractDataColumn
{
    protected ExpressionLanguage $expressionLanguage;

    protected array $options;

    /**
     * AbstractDataColumn constructor.
     */
    public function __construct(array $options)
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $this->options = $optionsResolver->resolve($options);
    }

    /**
     * @param Environment $templating
     * @param $data
     */
    abstract public function render($templating, $data): string;

    public function setExpressionLanguage(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
    }
}
