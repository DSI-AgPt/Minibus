<?php
namespace Minibus\Model\Browse\Formatters;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Minibus\Model\Browse\Formatters\IFormatter;
use Doctrine\ORM\AbstractQuery;
use Minibus\Util\Encoding\ArrayEncoder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;

abstract class AbstractDataTableFormatter implements ServiceLocatorAwareInterface, IFormatter
{
    
    use \Minibus\Util\Traits\ServiceLocatorAwareTrait;
    use \Minibus\Util\Traits\EntityManagerTrait;

    /**
     *
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Browse\Formatters\IFormatter::getData()
     */
    public function getData(array $columns, $start, $length, array $order, array $search, $year)
    {
        $response = array();
        $this->createQuery();
        $this->filterByCategory();
        $this->addSearchCriterium($search, $columns);
        $this->addOrder($order, $columns);
        $paginator = $this->getPaginator($start, $length);
        $objects = $paginator->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        $response['data'] = ArrayEncoder::utf8Converter($this->selectColumns($columns, $objects));
        $response['recordsTotal'] = count($paginator);
        $response['recordsFiltered'] = count($paginator);
        return $response;
    }

    /**
     *
     * @param array $columns            
     * @param array $objects            
     * @return array
     */
    private function selectColumns(array $columns, array $objects)
    {
        $selected = array();
        for ($i = 0; $i < count($objects); $i ++) {
            $personnel = $objects[$i];
            $selected[$i] = array();
            foreach ($columns as $column) {
                $name = $column['name'];
                if (array_key_exists($name, $personnel)) {
                    $value = $personnel[$name];
                    $selected[$i][$column['data']] = $value;
                }
            }
        }
        return $selected;
    }

    /**
     */
    abstract protected function createQuery();

    /**
     *
     * @param array $search            
     * @param array $columns            
     */
    abstract protected function addSearchCriterium(array $search, array $columns);

    /**
     *
     * @param array $order            
     * @param array $columns            
     */
    abstract protected function addOrder(array $orders, array $columns);

    /**
     *
     * @param int $start            
     * @param int $length            
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getPaginator($start, $length)
    {
        $query = $this->queryBuilder->setFirstResult($start)->setMaxResults($length);
        
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        
        return $paginator;
    }

    /**
     * 
     */
    abstract protected function filterByCategory();

    /**
     *
     * @return array
     */
    abstract protected function getCategories();
}