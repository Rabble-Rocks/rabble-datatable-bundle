<?php

namespace Rabble\DatatableBundle\Datatable\Row\Data\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

class LinkDataColumn extends GenericDataColumn
{
    /**
     * @param Environment $templating
     * @param $data
     */
    public function render($templating, $data): string
    {
        return $templating->render('@RabbleDatatable/Datatable/Data/Column/link.html.twig', [
            'text' => $this->expressionLanguage->evaluate($this->options['expression'], ['data' => $data]),
            'route' => $this->options['route'],
            'routeParams' => $this->expressionLanguage->evaluate($this->options['routeParamsExpression'], ['data' => $data]),
        ]);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(['route', 'routeParamsExpression']);
        $resolver->setAllowedTypes('route', 'string');
        $resolver->setAllowedTypes('routeParamsExpression', 'string');
    }
}
