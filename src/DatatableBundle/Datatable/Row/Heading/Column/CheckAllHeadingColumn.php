<?php

namespace Rabble\DatatableBundle\Datatable\Row\Heading\Column;

class CheckAllHeadingColumn extends AbstractHeadingColumn
{
    public function render($templating): string
    {
        return $templating->render('@RabbleDatatable/Datatable/Heading/Column/check_all.html.twig', ['id' => uniqid()]);
    }
}
