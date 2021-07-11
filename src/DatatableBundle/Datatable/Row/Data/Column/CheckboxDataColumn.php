<?php

namespace Rabble\DatatableBundle\Datatable\Row\Data\Column;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

class CheckboxDataColumn extends AbstractDataColumn
{
    /**
     * @param Environment $templating
     * @param $data
     */
    public function render($templating, $data): string
    {
        return $templating->render('@RabbleDatatable/Datatable/Data/Column/checkbox.html.twig', [
            'id' => $this->expressionLanguage->evaluate($this->options['expression'], ['data' => $data]),
            'uniqid' => uniqid(),
            'checkboxName' => $this->options['checkboxName'],
        ]);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('expression');
        $resolver->setDefault('checkboxName', 'item');
        $resolver->setAllowedTypes('expression', 'string');
        $resolver->setAllowedTypes('checkboxName', 'string');
    }
}
