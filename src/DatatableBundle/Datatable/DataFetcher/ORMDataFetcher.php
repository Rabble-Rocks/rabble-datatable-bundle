<?php

namespace Rabble\DatatableBundle\Datatable\DataFetcher;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Rabble\DatatableBundle\Datatable\AbstractGenericDatatable;
use Rabble\DatatableBundle\Datatable\Row\Data\Column\SearchableColumnInterface;
use Rabble\DatatableBundle\Datatable\Row\Data\Column\SortableColumnInterface;
use Rabble\DatatableBundle\Filter\GenericFilter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class ORMDataFetcher implements DataFetcherInterface, FilterApplicatorInterface
{
    private EntityManager $entityManager;

    private PaginatorInterface $paginator;

    public function __construct(EntityManager $em, PaginatorInterface $paginator)
    {
        $this->entityManager = $em;
        $this->paginator = $paginator;
    }

    /**
     * @param Environment $templating
     */
    public function fetch(AbstractGenericDatatable $datatable, $templating, Request $request): array
    {
        $qb = $this->entityManager->getRepository($datatable->getDataSource())->createQueryBuilder('a');

        if ($datatable->getEventDispatcher() instanceof EventDispatcherInterface) {
            $datatable->getEventDispatcher()->dispatch(new GenericEvent($qb), sprintf('datatable.%s.before_fetch_data', $datatable->getName()));
        }

        $search = $request->get('search');
        $order = $request->get('order');
        $length = min(100, max(10, $request->get('length')));

        $where = $qb->expr()->orX();
        foreach (array_values($datatable->getDataColumns()) as $i => $dataColumn) {
            if ($dataColumn instanceof SearchableColumnInterface && strlen($search['value'])) {
                $dataColumn->search($search['value'], $where, $this);
            }
            if ($dataColumn instanceof SortableColumnInterface && isset($order[0], $order[0]['column'], $order[0]['dir']) && $order[0]['column'] == $i) {
                $dir = ('asc' == $order[0]['dir'] ? 'ASC' : 'DESC');
                $dataColumn->sort($dir, $qb, $this);
            }
        }
        $countQb = clone $qb;
        $countQb->select('COUNT(a) AS count');
        if ($datatable->getEventDispatcher() instanceof EventDispatcherInterface) {
            $datatable->getEventDispatcher()->dispatch(new GenericEvent($countQb), sprintf('datatable.%s.before_count_data', $datatable->getName()));
        }
        $totalCount = $countQb->getQuery()->getResult()[0]['count'];
        if ($where->count()) {
            $qb->andWhere($where);
        }
        foreach ($datatable->getFilters() as $filter) {
            $filter->applyFilter($qb, $request);
        }

        $results = $this->paginator->paginate($qb, abs($request->get('start') / $length + 1), $length);
        $json = [
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $results->getTotalItemCount(),
        ];
        $data = [];
        foreach ($results as $result) {
            $dataRow = [];
            if (count($qb->getDQLPart('select')) > 1 && isset($result[0])) {
                $result = $result[0];
            }
            foreach ($datatable->getDataColumns() as $dataColumn) {
                $dataRow[] = $dataColumn->render($templating, $result);
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
        if (!$query instanceof Orx) {
            throw new \RuntimeException('The query should be an orx expression');
        }
        $qb = $this->entityManager->createQueryBuilder();
        if (0 == substr_count($field, '.')) {
            if ('?' == substr($field, 0, 1)) {
                $field = substr($field, 1);
            } else {
                $field = 'a.'.$field;
            }
        }
        $query->add($qb->expr()->like("AS_STRING({$field})", $qb->expr()->literal('%'.addcslashes($value, '%_').'%')));
    }

    /**
     * @param $query
     */
    public function sort(string $field, string $direction, $query): void
    {
        if (!$query instanceof QueryBuilder) {
            throw new \RuntimeException('The query should be a query builder');
        }
        if (0 == substr_count($field, '.')) {
            if ('?' == substr($field, 0, 1)) {
                $field = substr($field, 1);
            } else {
                $field = 'a.'.$field;
            }
        }
        $query->orderBy("AS_STRING({$field})", $direction);
    }

    /**
     * @param mixed $qb
     *
     * @throws \Exception
     *
     * @return mixed|void
     */
    public function applyFilter(GenericFilter $filter, $qb, Request $request)
    {
        $params = $request->query->get('dt_filter');
        if (!is_array($params)) {
            return;
        }
        if (!$qb instanceof QueryBuilder) {
            throw new \Exception('Expecting an ORM query builder.');
        }
        $name = $filter->getName();
        if (isset($params[$name]) && is_string($params[$name]) && strlen($params[$name])) {
            $filterQuery = $params[$name];
            if (0 == substr_count($name, '.')) {
                $name = 'a.'.$name;
            }
            if ($filter->isExactMatch()) {
                $qb->andWhere($qb->expr()->eq($name, $qb->expr()->literal($filterQuery)));
            } else {
                $qb->andWhere($qb->expr()->like($name, $qb->expr()->literal('%'.addcslashes($filterQuery, '%_').'%')));
            }
        }
    }
}
