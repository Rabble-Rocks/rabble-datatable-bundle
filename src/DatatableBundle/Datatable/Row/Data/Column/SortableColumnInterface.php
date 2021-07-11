<?php

namespace Rabble\DatatableBundle\Datatable\Row\Data\Column;

use Rabble\DatatableBundle\Datatable\DataFetcher\DataFetcherInterface;

/**
 * Sortable columns should implement this interface.
 */
interface SortableColumnInterface
{
    /**
     * We can sort by passing the request to the datafetcher or
     * implementing our own sorting algorithm.
     *
     * @param $direction
     * @param $query
     */
    public function sort($direction, $query, DataFetcherInterface $dataFetcher): void;
}
