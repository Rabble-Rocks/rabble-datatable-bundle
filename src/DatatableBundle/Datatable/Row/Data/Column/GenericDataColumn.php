<?php

namespace Rabble\DatatableBundle\Datatable\Row\Data\Column;

use Rabble\DatatableBundle\Datatable\DataFetcher\DataFetcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

class GenericDataColumn extends AbstractDataColumn implements SearchableColumnInterface, SortableColumnInterface
{
    /**
     * @param $value
     * @param $query
     */
    public function search($value, $query, DataFetcherInterface $dataFetcher): void
    {
        if ($this->options['searchCallback'] instanceof \Closure) {
            ($this->options['searchCallback'])($value, $query, $dataFetcher);

            return;
        }
        if (is_array($this->options['searchCallback'])) {
            call_user_func($this->options['searchCallback'], $value, $query, $dataFetcher);

            return;
        }
        if (null !== $this->options['searchField']) {
            $dataFetcher->search($this->options['searchField'], $value, $query);
        }
    }

    /**
     * @param $direction
     * @param $query
     */
    public function sort($direction, $query, DataFetcherInterface $dataFetcher): void
    {
        if ($this->options['sortCallback'] instanceof \Closure) {
            ($this->options['sortCallback'])($direction, $query, $dataFetcher);

            return;
        }
        if (is_array($this->options['sortCallback'])) {
            call_user_func($this->options['sortCallback'], $direction, $query, $dataFetcher);

            return;
        }
        if (null !== $this->options['sortField']) {
            $dataFetcher->sort($this->options['sortField'], $direction, $query);
        }
    }

    /**
     * @param Environment $templating
     * @param $data
     */
    public function render($templating, $data): string
    {
        return $templating->render('@RabbleDatatable/Datatable/Data/Column/generic.html.twig', [
            'text' => $this->expressionLanguage->evaluate($this->options['expression'], ['data' => $data]),
        ]);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('expression');
        $resolver->setAllowedTypes('expression', 'string');
        $resolver->setDefaults([
            'searchField' => null,
            'sortField' => null,
            'searchCallback' => null,
            'sortCallback' => null,
        ]);
        $resolver->setAllowedTypes('searchField', ['null', 'string']);
        $resolver->setAllowedTypes('sortField', ['null', 'string']);
        $resolver->setAllowedTypes('searchCallback', ['null', 'Closure', 'array']);
        $resolver->setAllowedTypes('sortCallback', ['null', 'Closure', 'array']);
    }
}
