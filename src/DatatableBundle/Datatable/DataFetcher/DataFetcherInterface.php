<?php

namespace Rabble\DatatableBundle\Datatable\DataFetcher;

use Rabble\DatatableBundle\Datatable\AbstractGenericDatatable;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

interface DataFetcherInterface
{
    /**
     * @param Environment $templating
     */
    public function fetch(AbstractGenericDatatable $datatable, $templating, Request $request): array;

    /**
     * @param $query
     */
    public function search(string $field, string $value, $query): void;

    /**
     * @param $query
     */
    public function sort(string $field, string $direction, $query): void;
}
