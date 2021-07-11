<?php

namespace Rabble\DatatableBundle\Datatable\Row\Data\Column;

use Rabble\DatatableBundle\Datatable\DataFetcher\DataFetcherInterface;

/**
 * Searchable columns should implement this interface.
 */
interface SearchableColumnInterface
{
    /**
     * The search method applies a global search query either by implementing
     * some search functionality or passing it directly to the data fetcher.
     *
     * @param $value
     * @param $query
     */
    public function search($value, $query, DataFetcherInterface $dataFetcher): void;
}
