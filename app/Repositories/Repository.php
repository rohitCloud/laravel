<?php
/**
 * @author Rohit Arora
 */

namespace App\Repositories;

use App\Contracts\Adapter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Request;

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
    protected $limit;
    protected $offset;
    protected $fields;
    protected $parameters;
    protected $sortBy;
    protected $sortType;
    protected $total;
    protected $data;

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
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @author Rohit Arora
     *
     * @param $total
     *
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = $total;

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
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @author Rohit Arora
     *
     * @return $this
     */
    public function setFields()
    {
        $this->fields = $this->Adapter->filter(isset($this->getParameters()['fields']) && $this->getParameters()['fields'] ? explode(',',
            $this->getParameters()['fields']) : ['*']);

        return $this;
    }

    /**
     * @author Rohit Arora
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @author Rohit Arora
     *
     * @param $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @author Rohit Arora
     *
     * @param $fields
     * @param $postList
     *
     * @return array
     */
    public function bindFields($fields, $postList)
    {
        return $this->Adapter->reFilter($fields, $postList);
    }

    /**
     * @author Rohit Arora
     *
     * @return $this
     */
    public function bindOffsetLimit()
    {
        $this->limit = (isset($this->getParameters()['limit']) && $this->getParameters()['limit'] > 0) ? (int) $this->getParameters()['limit'] : constant(get_called_class() . "::LIMIT");

        if ($this->limit > $this->getTotal()) {
            $this->limit = $this->getTotal();
        }

        $this->offset = isset($this->getParameters()['offset']) && $this->getParameters()['offset'] > 0 ? (int) $this->getParameters()['offset'] : constant(get_called_class() . "::OFFSET");

        if ($this->offset > $this->getTotal()) {
            $this->offset = $this->getTotal() - $this->limit;
        }

        return $this->limit($this->limit)
                    ->offset($this->offset);
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
     * @return $this
     */
    public function setOrder()
    {
        $this->sortBy   = isset($this->getParameters()['sort_by']) ? $this->getParameters()['sort_by'] : constant(get_called_class() . "::SORT_BY");
        $this->sortType = isset($this->getParameters()['sort_type']) ? $this->getParameters()['sort_type'] : constant(get_called_class() . "::SORT_TYPE");

        return $this->order($this->sortBy, $this->sortType);
    }

    /**
     * @author Rohit Arora
     *
     * @return array
     */
    public function process()
    {
        $this->setFields()
             ->setDataFromModel();

        $response['data'] = $this->bindFields($this->getFields(), $this->getData());

        // Use after getDataFromModel
        $response['total']  = $this->getTotal();
        $response['offset'] = $this->offset;
        $response['limit']  = $this->limit;

        if ($response['data']) {
            $response = array_merge($response, $this->processPages());
        }

        return $response;
    }

    /**
     * @author Rohit Arora
     *
     * @return array
     */
    public function setDataFromModel()
    {
        $this->setTotal($this->getModel()
                             ->count())
             ->setData($this->bindOffsetLimit()
                            ->setOrder()
                            ->fetch($this->getFields())
                            ->toArray());

        return $this;
    }

    /**
     * @author Rohit Arora
     *
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return $this
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @author Rohit Arora
     *
     */
    public function processPages()
    {
        $fields   = implode(',', $this->getFields());
        $response = [];

        $response['current_url'] = $this->getPage($fields, $this->sortBy, $this->sortType, $this->limit, $this->offset);

        if (!($this->limit + $this->offset >= $this->getTotal())) {
            $response['last_url'] = $this->getPage($fields, $this->sortBy, $this->sortType, $this->limit,
                (($this->getTotal() - $this->limit < $this->offset) ? $this->offset : ($this->getTotal() - $this->limit)));
        }

        if ($this->offset > 0) {
            $response['previous_url'] = $this->getPage($fields, $this->sortBy, $this->sortType, $this->limit,
                ($this->offset - $this->limit) > 0 ? ($this->offset - $this->limit) : 0);

            $response['first_url'] = $this->getPage($fields, $this->sortBy, $this->sortType, $this->limit, 0);
        }

        if ($this->limit < $this->getTotal() && !($this->limit + $this->offset >= $this->getTotal())) {
            $response['next_url'] = $this->getPage($fields, $this->sortBy, $this->sortType, $this->limit,
                (($this->offset + $this->limit) < $this->getTotal()) ? $this->offset + $this->limit : $this->getTotal() - $this->limit);
        }

        return $response;
    }

    /**
     * @author Rohit Arora
     *
     * @param $fields
     * @param $sortBy
     * @param $sortType
     * @param $limit
     * @param $offset
     *
     * @return string
     */
    private function getPage($fields, $sortBy, $sortType, $limit, $offset)
    {
        return Request::url() . '?fields=' . $fields . '&sort_by=' . $sortBy . '&sort_type=' . $sortType . '&limit=' . $limit . '&offset=' . $offset;
    }
}