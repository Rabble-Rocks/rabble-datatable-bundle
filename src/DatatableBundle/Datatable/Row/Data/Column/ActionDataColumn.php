<?php

namespace Rabble\DatatableBundle\Datatable\Row\Data\Column;

use Rabble\DatatableBundle\Datatable\Row\Data\Column\Action\Action;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

class ActionDataColumn extends AbstractDataColumn
{
    /**
     * @param Environment $templating
     * @param $data
     */
    public function render($templating, $data): string
    {
        $actions = '';
        /** @var Action $action */
        foreach ($this->options['actions'] as $action) {
            $active = $action->getActive();
            if (is_bool($active) && !$active) {
                continue;
            }
            if (is_string($active)) {
                $active = $this->expressionLanguage->evaluate($active, ['data' => $data]);
                if (!$active) {
                    continue;
                }
            }
            $icon = $action->getIcon();
            if ('?' == substr($icon, 0, 1)) {
                $icon = $this->expressionLanguage->evaluate(substr($icon, 1), ['data' => $data]);
            }
            $url = $this->expressionLanguage->evaluate($action->getUrl(), ['data' => $data]);
            $attributes = $action->getAttributes();
            foreach ($attributes as $name => $value) {
                if ('?' == substr($value, 0, 1)) {
                    $attributes[$name] = $this->expressionLanguage->evaluate(substr($value, 1), ['data' => $data]);
                }
            }
            $actions .= $templating->render('@RabbleDatatable/Datatable/Data/Column/Action/action.html.twig', [
                'icon' => $icon,
                'url' => $url,
                'attributes' => $attributes,
            ]);
        }

        return $templating->render('@RabbleDatatable/Datatable/Data/Column/action.html.twig', [
            'actions' => $actions,
        ]);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('actions');
        $resolver->setAllowedTypes('actions', 'array');
    }
}
