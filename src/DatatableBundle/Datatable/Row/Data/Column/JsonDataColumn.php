<?php

namespace Rabble\DatatableBundle\Datatable\Row\Data\Column;

use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Rabble\DatatableBundle\Datatable\DataFetcher\DataFetcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Allows displaying and searching data in SQL JSON fields.
 * Using this method, we can create schema-less entities that may serve multiple purposes.
 * In order to use this class, you must register JSON_EXTRACT as a function in Doctrine.
 * Easiest way of doing this would be by installing `scienta/doctrine-json-functions`.
 *
 * `composer require scienta/doctrine-json-functions`
 * Follow instructions at https://github.com/ScientaNL/DoctrineJsonFunctions
 *
 * $dataColumns[] = new JsonDataColumn([
 *   'expression' => "data.getProperties()['foo']",
 *   'property' => "a.properties",
 *   'sortField' => 'foo',
 *   'searchField' => 'foo',
 * ]);
 */
class JsonDataColumn extends AbstractDataColumn implements SearchableColumnInterface, SortableColumnInterface
{
    /**
     * @param $value
     * @param $query
     */
    public function search($value, $query, DataFetcherInterface $dataFetcher): void
    {
        if (!$query instanceof Orx) {
            throw new \RuntimeException('The query should be a query builder');
        }
        if (null !== $this->options['searchField'] && null !== $this->options['property']) {
            $query->add('JSON_EXTRACT('.$this->options['property'].", '$.".addcslashes($this->options['searchField'], "'")."') LIKE '%".addcslashes($value, "'%_")."%'");
        }
    }

    /**
     * @param $direction
     * @param $query
     */
    public function sort($direction, $query, DataFetcherInterface $dataFetcher): void
    {
        if (!$query instanceof QueryBuilder) {
            throw new \RuntimeException('The query should be a query builder');
        }
        if (null !== $this->options['sortField'] && null !== $this->options['property']) {
            $query->orderBy('JSON_EXTRACT('.$this->options['property'].", '$.".addcslashes($this->options['sortField'], "'")."')", $direction);
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
            'property' => null,
            'searchField' => null,
            'sortField' => null,
        ]);
        $resolver->setAllowedTypes('property', ['null', 'string']);
        $resolver->setAllowedTypes('searchField', ['null', 'string']);
        $resolver->setAllowedTypes('sortField', ['null', 'string']);

        $resolver->setNormalizer('property', function (OptionsResolver $resolver, $property) {
            if (null !== $property && 0 == substr_count($property, '.')) {
                return 'a.'.$property;
            }

            return $property;
        });
    }
}
