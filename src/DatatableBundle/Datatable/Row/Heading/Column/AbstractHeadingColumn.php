<?php

namespace Rabble\DatatableBundle\Datatable\Row\Heading\Column;

abstract class AbstractHeadingColumn
{
    public $sortable = true;

    abstract public function render($templating): string;
}
