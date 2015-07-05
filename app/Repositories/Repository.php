<?php
/**
 * @author Rohit Arora
 */

namespace App\Repositories;

use App\Contracts\Adapter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @author  Rohit Arora
 *
 * Class Repositories
 * @package App
 */
abstract class Repository
{
    const SORT_ASC  = 'asc';
    const SORT_DESC = 'desc';

    /**
     * @var Model
     */
    protected $Model;

    /** @var  Model */
    private $QueryBuilder;
    /**
     * @var Adapter
     */
    protected $Adapter;

    /**
     * @param Model   $Model
     * @param Adapter $Adapter
     */
    public function __construct(Model $Model, Adapter $Adapter)
    {
        $this->Model = $Model;
        $this->setQueryBuilder($this->getModel());
        $this->Adapter = $Adapter;
    }

    /**
     * @author Rohit Arora
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->Model;
    }

    /**
     * @author Rohit Arora
     *
     * @param Model $Model
     */
    public function setModel($Model)
    {
        $this->Model = $Model;
    }

    /**
     * @author Rohit Arora
     *
     * @return mixed
     */
    public function getQueryBuilder()
    {
        return $this->QueryBuilder;
    }

    /**
     * @author Rohit Arora
     *
     * @param $QueryBuilder
     *
     * @return Model
     */
    public function setQueryBuilder($QueryBuilder)
    {
        $this->QueryBuilder = $QueryBuilder;

        return $this;
    }

    /**
     * @author Rohit Arora
     *
     * @param $offset
     *
     * @return Builder
     */
    public function offset($offset)
    {
        return $this->setQueryBuilder($this->getQueryBuilder()
                                           ->skip($offset));
    }

    /**
     * @author Rohit Arora
     *
     * @param $limit
     *
     * @return Builder
     */
    public function limit($limit)
    {
        return $this->setQueryBuilder($this->getQueryBuilder()
                                           ->take($limit));
    }

    /**
     * @author Rohit Arora
     *
     * @param $by
     * @param $type
     *
     * @return Model
     */
    public function order($by = 'id', $type = self::SORT_ASC)
    {
        if (!self::isValidOrderType($type) || !$this->getModel()
                                                    ->isValidOrderBy($by)
        ) {
            return $this;
        }

        return $this->setQueryBuilder($this->getQueryBuilder()
                                           ->orderBy($by, $type));
    }

    /**
     * @author Rohit Arora
     *
     * @param array $columns
     *
     * @return Collection
     */
    public function fetch($columns = ['*'])
    {
        return $this->getQueryBuilder()
                    ->get($columns);
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return array
     */
    public function getFields($parameters)
    {
        $parameters = $this->Adapter->filter(isset($parameters['fields']) ? explode(',', $parameters['fields']) : ['*']);
        return $parameters;
    }

    /**
     * @author Rohit Arora
     *
     * @param $fields
     * @param $postList
     *
     * @return array
     */
    public function bindData($fields, $postList)
    {
        return $this->Adapter->reFilter($fields, $postList);
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return $this
     */
    public function bindOffsetLimit($parameters)
    {
        $limit  = isset($parameters['limit']) ? (int) $parameters['limit'] : constant(get_called_class() . "::LIMIT");
        $offset = isset($parameters['offset']) ? (int) $parameters['offset'] : constant(get_called_class() . "::OFFSET");

        return $this->limit($limit)
                    ->offset($offset);
    }

    /**
     * @author Rohit Arora
     *
     * @param $type
     *
     * @return bool
     */
    public static function isValidOrderType($type)
    {
        return in_array($type, [self::SORT_ASC, self::SORT_DESC]);
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return $this
     */
    public function setOrder($parameters)
    {
        $by   = isset($parameters['sort_by']) ? $parameters['sort_by'] : constant(get_called_class() . "::SORT_BY");
        $type = isset($parameters['sort_type']) ? $parameters['sort_type'] : constant(get_called_class() . "::SORT_TYPE");

        return $this->order($by, $type);
    }


    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return array
     */
    public function process($parameters)
    {
        $fields = $this->getFields($parameters);

        if (!$fields) {
            return [];
        }

        $list = $this->bindOffsetLimit($parameters)
                     ->setOrder($parameters)
                     ->fetch($fields)
                     ->toArray();

        return $this->bindData($fields, $list);
    }
}