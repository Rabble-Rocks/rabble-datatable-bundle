<?php

namespace Rabble\DatatableBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use Rabble\DatatableBundle\Datatable\AbstractDatatable;
use Rabble\DatatableBundle\Datatable\AbstractGenericDatatable;
use Symfony\Component\Form\FormFactoryInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * This twig extension allows us to render a datatable
 * directly in twig without the interference of a controller.
 */
class DatatableExtension extends AbstractExtension
{
    protected ArrayCollection $datatables;

    protected FormFactoryInterface $formFactory;

    /**
     * DatatableExtension constructor.
     */
    public function __construct(ArrayCollection $datatables, FormFactoryInterface $formFactory)
    {
        $this->datatables = $datatables;
        $this->formFactory = $formFactory;
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('datatable_exists', [$this, 'datatableExists']),
            new TwigFunction('datatable', [$this, 'datatable'], ['is_safe' => ['html']]),
            new TwigFunction('datatable_filters', [$this, 'datatableFilters'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function datatableExists($name)
    {
        return $this->datatables->containsKey($name);
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function datatable($name)
    {
        $datatable = $this->datatables->get($name);
        if (!$datatable instanceof AbstractDatatable) {
            throw new \RuntimeException('The given datatable name does not resolve to a valid datatable');
        }

        return $datatable->render();
    }

    /**
     * @param $name
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     *
     * @return string
     */
    public function datatableFilters(Environment $environment, $name)
    {
        $datatable = $this->datatables->get($name);
        if ($datatable instanceof AbstractGenericDatatable && null !== $datatable->getReplacement()) {
            return $this->datatableFilters($environment, $datatable->getReplacement());
        }
        if (!$datatable instanceof AbstractDatatable) {
            throw new \RuntimeException('The given datatable name does not resolve to a valid datatable');
        }
        $builder = $this->formFactory->createNamedBuilder('dt_filter');
        foreach ($datatable->getFilters() as $filter) {
            $filter->buildForm($builder);
        }

        return $environment->render('@RabbleDatatable/Filter/filter.html.twig', [
            'form' => $builder->getForm()->createView(),
            'datatable' => $datatable,
        ]);
    }
}
