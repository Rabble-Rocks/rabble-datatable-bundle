<?php

namespace Rabble\DatatableBundle\Filter;

use Rabble\DatatableBundle\Datatable\DataFetcher\FilterApplicatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenericFilter implements FilterInterface
{
    protected array $options;

    /**
     * GenericFilter constructor.
     *
     * @param string        $name
     * @param string        $label
     * @param null|\Closure $filterCallback
     */
    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilter($query, Request $request): void
    {
        if ($this->options['lowerCase']) {
            $params = $request->query->get('dt_filter');
            if (is_array($params)) {
                $name = $this->options['name'];
                if (isset($params[$name]) && is_string($params[$name]) && strlen($params[$name])) {
                    $params[$name] = strtolower($params[$name]);
                    $request->query->set('dt_filter', $params);
                }
            }
        }
        if ($this->options['filterCallback'] instanceof \Closure) {
            ($this->options['filterCallback'])($this, $query, $request);
        } else {
            $this->options['dataFetcher']->applyFilter($this, $query, $request);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder): void
    {
        $builder->add($this->options['name'], TextType::class, [
            'label' => $this->options['label'],
            'required' => false,
            'attr' => [
                'autocomplete' => 'off',
            ],
            'translation_domain' => $this->options['translationDomain'],
        ]);
    }

    public function getName(): string
    {
        return $this->options['name'];
    }

    /**
     * @return bool
     */
    public function isExactMatch()
    {
        return $this->options['exactMatch'];
    }

    /**
     * @return OptionsResolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['name', 'label']);
        $resolver->setDefaults([
            'filterCallback' => null,
            'dataFetcher' => null,
            'exactMatch' => false,
            'lowerCase' => true,
            'translationDomain' => 'messages',
        ]);
        $resolver->setAllowedTypes('translationDomain', 'string');
        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('label', 'string');
        $resolver->setAllowedTypes('dataFetcher', ['null', FilterApplicatorInterface::class]);
        $resolver->setAllowedTypes('filterCallback', ['null', 'Closure', 'array']);
        $resolver->setNormalizer('filterCallback', function (Options $options, $value) {
            if (null === $value && null === $options['dataFetcher']) {
                throw new \RuntimeException('You need to provide a filterCallback option or dataFetcher option in order to allow filtering.');
            }
            if (is_array($value)) {
                return function ($filter, $query, $request) use ($value) {
                    call_user_func($value, $filter, $query, $request);
                };
            }

            return $value;
        });

        return $resolver;
    }
}
