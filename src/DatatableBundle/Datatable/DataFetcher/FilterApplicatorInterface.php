<?php

namespace Rabble\DatatableBundle\Datatable\DataFetcher;

use Rabble\DatatableBundle\Filter\GenericFilter;
use Symfony\Component\HttpFoundation\Request;

interface FilterApplicatorInterface
{
    /**
     * @param mixed $query
     *
     * @return mixed
     */
    public function applyFilter(GenericFilter $filter, $query, Request $request);
}
