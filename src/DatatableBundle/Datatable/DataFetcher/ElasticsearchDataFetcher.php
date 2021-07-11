<?php

namespace Rabble\DatatableBundle\Datatable\DataFetcher;

use ONGR\ElasticsearchBundle\Service\IndexService;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\WildcardQuery;
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use Rabble\DatatableBundle\Datatable\AbstractGenericDatatable;
use Rabble\DatatableBundle\Datatable\Row\Data\Column\SearchableColumnInterface;
use Rabble\DatatableBundle\Datatable\Row\Data\Column\SortableColumnInterface;
use Rabble\DatatableBundle\Filter\GenericFilter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class ElasticsearchDataFetcher implements DataFetcherInterface, FilterApplicatorInterface
{
    private IndexService $index;

    public function __construct(IndexService $index)
    {
        $this->index = $index;
    }

    /**
     * @param Environment $templating
     */
    public function fetch(AbstractGenericDatatable $datatable, $templating, Request $request): array
    {
        $search = $this->index->createSearch();
        if ($datatable->getEventDispatcher() instanceof EventDispatcherInterface) {
            $datatable->getEventDispatcher()->dispatch(new GenericEvent($search), sprintf('datatable.%s.before_fetch_data', $datatable->getName()));
        }

        $searchQuery = $request->get('search');
        $order = $request->get('order');
        $length = min(100, max(10, $request->get('length')));
        $search->addQuery($bool = new BoolQuery());
        $countSearch = clone $search;
        if ($datatable->getEventDispatcher() instanceof EventDispatcherInterface) {
            $datatable->getEventDispatcher()->dispatch(new GenericEvent($countSearch), sprintf('datatable.%s.before_count_data', $datatable->getName()));
        }
        $countResult = $this->index->search($countSearch->toArray());

        $totalCount = $countResult['hits']['total']['value'];
        foreach (array_values($datatable->getDataColumns()) as $i => $dataColumn) {
            if ($dataColumn instanceof SearchableColumnInterface && strlen($searchQuery['value'])) {
                $dataColumn->search($searchQuery['value'], $bool, $this);
            }
            if ($dataColumn instanceof SortableColumnInterface && isset($order[0], $order[0]['column'], $order[0]['dir']) && $order[0]['column'] == $i) {
                $dir = ('asc' == $order[0]['dir'] ? 'ASC' : 'DESC');
                $dataColumn->sort($dir, $search, $this);
            }
        }
        foreach ($datatable->getFilters() as $filter) {
            $filter->applyFilter($search, $request);
        }
        $start = abs($request->get('start'));
        $search->setFrom($start);
        $search->setSize($length);

        $results = $this->index->search($search->toArray());
        $json = [
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $results['hits']['total']['value'],
        ];
        $data = [];
        foreach ($results['hits']['hits'] as $result) {
            $source = $result['_source'];
            $source['id'] = $result['_id'];
            $dataRow = [];
            foreach ($datatable->getDataColumns() as $dataColumn) {
                $dataRow[] = $dataColumn->render($templating, $source);
            }
            $data[] = $dataRow;
        }
        $json['data'] = $data;

        return $json;
    }

    /**
     * @param $query
     */
    public function search(string $field, string $value, $query): void
    {
        if (!$query instanceof BoolQuery) {
            throw new \RuntimeException('The query should be a bool query');
        }
        $query->add(new WildcardQuery($field, "*{$value}*"));
    }

    /**
     * @param $query
     */
    public function sort(string $field, string $direction, $query): void
    {
        if (!$query instanceof Search) {
            throw new \RuntimeException('The query should be a search');
        }
        $query->addSort(new FieldSort($field, $direction));
    }

    /**
     * @param mixed $query
     *
     * @throws \Exception
     *
     * @return mixed|void
     */
    public function applyFilter(GenericFilter $filter, $query, Request $request)
    {
        $params = $request->query->get('dt_filter');
        if (!is_array($params)) {
            return;
        }
        if (!$query instanceof BoolQuery) {
            throw new \Exception('Expecting a BoolQuery.');
        }
        $name = $filter->getName();
        if (isset($params[$name]) && is_string($params[$name]) && strlen($params[$name])) {
            $filterQuery = $params[$name];
            if ($filter->isExactMatch()) {
                $query->add(new MatchQuery($name, $filterQuery));
            } else {
                $query->add(new MatchQuery($name, $filterQuery));
            }
        }
    }
}
