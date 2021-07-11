<?php

namespace Rabble\DatatableBundle\Datatable;

use Rabble\DatatableBundle\Filter\FilterInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractDatatable
{
    protected array $configuration = [];

    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Get the name of the data table.
     */
    public function getName(): string
    {
        $name = preg_replace('/^.+\\\([a-zA-Z]+)((Datatable)?)$/U', '$1', static::class);

        return strtolower(preg_replace('/([a-z])([A-Z]+)/', '$1_$2', $name));
    }

    /**
     * @return FilterInterface[]
     */
    public function getFilters(): array
    {
        return [];
    }

    abstract public function buildData(Request $request): array;

    abstract public function render(): string;
}
