<?php

namespace Rabble\DatatableBundle\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface all filters should implement.
 */
interface FilterInterface
{
    /**
     * Apply the filter using the query provided.
     *
     * @param mixed $query
     */
    public function applyFilter($query, Request $request): void;

    public function buildForm(FormBuilderInterface $builder): void;
}
