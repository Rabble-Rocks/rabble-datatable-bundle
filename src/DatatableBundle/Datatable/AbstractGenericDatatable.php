<?php

namespace Rabble\DatatableBundle\Datatable;

use Rabble\DatatableBundle\Datatable\DataFetcher\DataFetcherInterface;
use Rabble\DatatableBundle\Datatable\Row\Data\Column\AbstractDataColumn;
use Rabble\DatatableBundle\Datatable\Row\Heading\Column\AbstractHeadingColumn;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

abstract class AbstractGenericDatatable extends AbstractDatatable
{
    protected Environment $templating;

    /**
     * @var AbstractHeadingColumn[]
     */
    protected array $headingColumns;

    /**
     * @var AbstractDataColumn[]
     */
    protected array $dataColumns;

    /**
     * @var mixed
     */
    protected $dataSource;

    private ?EventDispatcherInterface $eventDispatcher;

    private array $options = [];

    private ExpressionLanguage $expressionLanguage;

    public function setConfiguration(array $configuration)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->configuration = $resolver->resolve($configuration);
    }

    /**
     * @param Environment $templating
     *
     * @return AbstractGenericDatatable
     */
    public function setTemplating($templating)
    {
        $this->templating = $templating;

        return $this;
    }

    /**
     * @return null|EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @return AbstractGenericDatatable
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    public function setExpressionLanguage(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return AbstractGenericDatatable
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return AbstractDataColumn[]
     */
    public function getDataColumns()
    {
        return $this->dataColumns;
    }

    /**
     * @return mixed
     */
    public function getDataSource()
    {
        return $this->configuration['data_source'];
    }

    /**
     * Allows you to specify a replacement datatable last-minute.
     * Return the name of the replacement datatable or null if
     * you don't want to use a replacement.
     */
    public function getReplacement(): ?string
    {
        return null;
    }

    public function buildData(Request $request): array
    {
        if (null !== $this->getReplacement()) {
            return [];
        }
        $this->initialize();

        foreach ($this->dataColumns as $dataColumn) {
            $dataColumn->setExpressionLanguage($this->expressionLanguage);
        }

        return array_merge([
            'draw' => $request->get('draw'),
        ], $this->configuration['data_fetcher']->fetch($this, $this->templating, $request));
    }

    public function render(): string
    {
        if (null !== $this->getReplacement()) {
            return $this->templating->render('@RabbleDatatable/Datatable/replacement.html.twig', [
                'replacement' => $this->getReplacement(),
            ]);
        }
        $this->initialize();
        $heading = '';
        foreach ($this->headingColumns as $column) {
            $heading .= $column->render($this->templating);
        }
        $heading = $this->templating->render('@RabbleDatatable/Datatable/Heading/generic_heading.html.twig', [
            'columns' => $heading,
        ]);

        return $this->templating->render('@RabbleDatatable/Datatable/generic.html.twig', [
            'datatable' => $this,
            'heading' => $heading,
            'options' => json_encode($this->options),
        ]);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'data_fetcher',
        ]);
        $resolver->setDefault('data_source', null);
        $resolver->setAllowedTypes('data_fetcher', [DataFetcherInterface::class]);
    }

    /**
     * Overridden in child classes.
     */
    abstract protected function initialize(): void;
}
