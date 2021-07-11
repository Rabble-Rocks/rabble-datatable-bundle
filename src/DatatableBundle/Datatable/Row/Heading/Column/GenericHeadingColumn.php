<?php

namespace Rabble\DatatableBundle\Datatable\Row\Heading\Column;

use Twig\Environment;

class GenericHeadingColumn extends AbstractHeadingColumn
{
    private string $text;

    /**
     * @var bool|string
     */
    private $translationDomain;

    private array $attributes;

    /**
     * GenericHeadingColumn constructor.
     *
     * @param bool|string $translationDomain
     */
    public function __construct(string $text, $translationDomain = 'datatable', array $attributes = [])
    {
        $this->text = $text;
        $this->translationDomain = $translationDomain;
        $this->attributes = $attributes;
    }

    /**
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function addAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param $name
     */
    public function removeAttribute($name)
    {
        unset($this->attributes[$name]);

        return $this;
    }

    /**
     * @param $property
     * @param $value
     *
     * @return $this
     */
    public function css($property, $value)
    {
        if (!isset($this->attributes['style'])) {
            $this->attributes['style'] = [];
        }
        if (null === $value) {
            unset($this->attributes['style'][$property]);

            return $this;
        }
        $this->attributes['style'][$property] = $value;

        return $this;
    }

    /**
     * @param Environment $templating
     */
    public function render($templating): string
    {
        if (!$templating instanceof EngineInterface && !$templating instanceof Environment) {
            throw new \InvalidArgumentException(sprintf('Expecting an instance of %s', Environment::class));
        }
        if (isset($this->attributes['style']) && is_array($this->attributes['style'])) {
            $style = '';
            foreach ($this->attributes['style'] as $property => $value) {
                $style .= sprintf('%s: %s;', $property, (is_int($value) ? sprintf('%spx', $value) : $value));
            }
            $this->attributes['style'] = $style;
        }
        if (!$this->sortable) {
            $this->attributes['data-sortable'] = 'false';
        }

        return $templating->render('@RabbleDatatable/Datatable/Heading/Column/generic.html.twig', [
            'text' => $this->text,
            'attributes' => $this->attributes,
            'translationDomain' => $this->translationDomain,
        ]);
    }
}
