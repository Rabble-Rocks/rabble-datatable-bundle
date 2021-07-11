<?php

namespace Rabble\DatatableBundle\Datatable\Row\Data\Column\Action;

/**
 * Represents an action in ActionDataColumn.
 */
final class Action
{
    private string $url;

    private string $icon;

    /**
     * @var bool|string
     */
    private $active;

    private array $attributes;

    /**
     * Action constructor.
     *
     * @param bool|string $active
     */
    public function __construct(string $url, string $icon, $active = true, array $attributes = [])
    {
        $this->url = $url;
        $this->icon = $icon;
        $this->active = $active;
        $this->attributes = $attributes;
    }

    /**
     * @return bool|string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool|string $active
     *
     * @return Action
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
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
    public function addAttribute(string $name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param $name
     */
    public function removeAttribute(string $name)
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
    public function css(string $property, string $value)
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

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
