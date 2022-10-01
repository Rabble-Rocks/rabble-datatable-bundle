<?php

namespace Rabble\DatatableBundle\ExpressionLanguage;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

class ExpressionLanguage extends BaseExpressionLanguage
{
    private array $injectedVariables = [];

    private ?CacheItemPoolInterface $cache;

    /**
     * ExpressionLanguage constructor.
     */
    public function __construct(CacheItemPoolInterface $cache = null, array $providers = [])
    {
        parent::__construct($cache, $providers);
        $this->cache = $cache;
    }

    public function getInjectedVariables(): array
    {
        return $this->injectedVariables;
    }

    /**
     * @param $name
     * @param $value
     */
    public function addVariable($name, $value)
    {
        $this->injectedVariables[$name] = $value;
    }

    public function registerVariableProvider(VariableProviderInterface $provider)
    {
        foreach ($provider->getVariables() as $name => $value) {
            $this->addVariable($name, $value);
        }
    }

    public function compile(Expression|string $expression, array $names = []): string
    {
        foreach (array_keys($this->injectedVariables) as $name) {
            $names[] = $name;
        }

        return parent::compile($expression, $names);
    }

    public function evaluate(Expression|string $expression, array $values = []): mixed
    {
        $names = array_keys($values) + array_keys($this->injectedVariables);
        $namesHash = hash('md5', implode(',', $names));
        $expressionHash = hash('md5', $expression);
        $cacheKey = 'datatable_expression_'.$expressionHash.$namesHash;
        $cacheItem = $this->cache->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            $compiled = $cacheItem->get();
        } else {
            $compiled = $this->compile($expression, $names);
            $cacheItem->set($compiled);
            $this->cache->save($cacheItem);
        }
        foreach (array_merge($values, $this->injectedVariables) as $name => $value) {
            ${$name} = $value;
        }

        return eval(sprintf('return %s;', $compiled));
    }
}
