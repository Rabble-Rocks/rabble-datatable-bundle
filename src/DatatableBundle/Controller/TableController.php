<?php

namespace Rabble\DatatableBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TableController extends AbstractController
{
    private ArrayCollection $datatables;

    /**
     * TableController constructor.
     */
    public function __construct(ArrayCollection $datatables)
    {
        $this->datatables = $datatables;
    }

    public function indexAction(Request $request, $datatable)
    {
        return new JsonResponse($this->datatables->get($datatable)->buildData($request));
    }
}
