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
}