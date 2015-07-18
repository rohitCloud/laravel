<?php
/**
 * @author Rohit Arora
 */

namespace App\Repositories;

use App\Adapters\Response;
use App\Contracts\Adapter;
use App\Exceptions\InvalidArguments;
use App\Exceptions\InvalidData;
use App\Exceptions\NotFound;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @author  Rohit Arora
 *
 * Class Repositories
 * @package App
 */
abstract class Base
{
    const DEFAULT_OFFSET    = 0;
    const DEFAULT_LIMIT     = 10;
    const DEFAULT_SORT_BY   = self::ID;
    const DEFAULT_SORT_TYPE = Base::SORT_ASC;

    const ID           = 'id';
    const SORT_ASC     = 'asc';
    const SORT_DESC    = 'desc';
    const LIMIT        = 'limit';
    const OFFSET       = 'offset';
    const SORT_BY      = 'sort_by';
    const SORT_TYPE    = 'sort_type';
    const FIELDS       = 'fields';
    const EMBED        = 'embed';
    const TOTAL        = 'total';
    const CURRENT_URL  = 'current_url';
    const LAST_URL     = 'last_url';
    const PREVIOUS_URL = 'previous_url';
    const FIRST_URL    = 'first_url';
    const NEXT_URL     = 'next_url';

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
    protected $embed;

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
     * @return $this
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
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @author Rohit Arora
     *
     * @param array $data
     *
     * @return $this
     * @throws \Exception
     */
    public function setData(array $data)
    {
        if (!$data) {
            throw new NotFound;
        }

        $this->data = $data;

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
     * @param string $by
     * @param string $type
     *
     * @return Model
     * @throws InvalidArguments
     */
    public function order($by = self::ID, $type = self::SORT_ASC)
    {
        if (!self::isValidOrderType($type) || !static::isValidOrderBy($by)) {
            throw new InvalidArguments;
        }

        return $this->setQueryBuilder($this->getQueryBuilder()
                                           ->orderBy($by, $type));
    }

    /**
     * @author Rohit Arora
     *
     * @return $this
     */
    public function bindOffsetLimit()
    {
        $this->limit = (isset($this->getParameters()[self::LIMIT]) && $this->getParameters()[self::LIMIT] > 0) ? (int) $this->getParameters()[self::LIMIT] :
            static::DEFAULT_LIMIT;

        if ($this->limit > $this->getTotal()) {
            $this->limit = $this->getTotal();
        }

        $this->offset = isset($this->getParameters()[self::OFFSET]) && $this->getParameters()[self::OFFSET] > 0 ? (int) $this->getParameters()[self::OFFSET] :
            static::DEFAULT_OFFSET;

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
        $this->sortBy   = isset($this->getParameters()[self::SORT_BY]) ? $this->getParameters()[self::SORT_BY] : static::DEFAULT_SORT_BY;
        $this->sortType = isset($this->getParameters()[self::SORT_TYPE]) ? $this->getParameters()[self::SORT_TYPE] : static::DEFAULT_SORT_TYPE;

        return $this->order($this->sortBy, $this->sortType);
    }

    /**
     * @author Rohit Arora
     *
     * @return $this
     */
    public function setFields()
    {
        $this->fields = $this->Adapter->getModelFields(isset($this->getParameters()[self::FIELDS]) && $this->getParameters()[self::FIELDS] ? explode(COMMA,
            $this->getParameters()[self::FIELDS]) : [ALL_FIELDS]);

        return $this;
    }

    /**
     * @author Rohit Arora
     *
     * @return $this
     */
    public function setFieldsWithData()
    {
        $this->fields = $this->Adapter->getModelFieldsWithData($this->getParameters());

        return $this;
    }

    /**
     * @author Rohit Arora
     *
     * @return mixed
     */
    public function getFilteredFields()
    {
        return $this->Adapter->getFilteredFields($this->fields);
    }

    /**
     * @author Rohit Arora
     *
     * @return $this
     */
    private function setEmbed()
    {
        $this->embed = isset($this->getParameters()[self::EMBED]) ? $this->getParameters()[self::EMBED] : '';

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
    public function setRequestParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this->setEmbed()
                    ->setFields();
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return $this
     */
    public function setPostParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this->setFieldsWithData();
    }

    /**
     * @author Rohit Arora
     *
     * @param $id
     *
     * @return array
     * @throws \Exception
     */
    public function find($id)
    {
        if (!$this->fields) {
            throw new InvalidArguments;
        }

        /** @var Model $data */
        $data = $this->getQueryBuilder()
                     ->find($id);

        if (!$data) {
            throw new NotFound;
        }

        return $this->setData($data->toArray())
                    ->process(true);
    }

    /**
     * @author Rohit Arora
     *
     * @return array
     * @throws \Exception
     */
    public function get()
    {
        if (!$this->fields) {
            throw new InvalidArguments;
        }

        $total = $this->getQueryBuilder()
                      ->count();

        if (!$total) {
            throw new NotFound;
        }

        $this->setTotal($total);

        /** @var Model $data */
        $data = $this->bindOffsetLimit()
                     ->setOrder()
                     ->getQueryBuilder()
                     ->get($this->fields);

        if (!$data) {
            throw new NotFound;
        }

        $this->setData($data->toArray());

        return $this->process();
    }

    /**
     * @author Rohit Arora
     *
     * @throws InvalidArguments
     *
     * @return array
     */
    public function save()
    {
        if (!$this->fields) {
            throw new InvalidArguments;
        }
    }

    /**
     * @author Rohit Arora
     *
     * @param bool|false $single
     *
     * @return array
     */
    public function process($single = false)
    {
        $response[Response::DATA] = $this->bindFields($this->getFilteredFields(), $this->getData(), $single);

        if (!$single) {
            // Use after getDataFromModel
            $response[self::TOTAL]  = $this->getTotal();
            $response[self::OFFSET] = $this->offset;
            $response[self::LIMIT]  = $this->limit;

            if ($response[Response::DATA]) {
                $response = array_merge($response, $this->processPages());
            }
        }

        return $response;
    }

    /**
     * @author Rohit Arora
     *
     * @param $fields
     * @param $data
     * @param $single
     *
     * @return array|bool
     */
    public function bindFields($fields, $data, $single)
    {
        return $this->Adapter->reFilter($fields, $data, $single, explode(COMMA, $this->embed));
    }

    /**
     * @author Rohit Arora
     *
     * @return array
     */
    public function processPages()
    {
        $fields   = implode(COMMA, $this->getFilteredFields());
        $response = [];

        $response[self::CURRENT_URL] = $this->getPage($fields, $this->sortBy, $this->sortType, $this->limit, $this->offset, $this->embed);

        if (!($this->limit + $this->offset >= $this->getTotal())) {
            $response[self::LAST_URL] = $this->getPage($fields, $this->sortBy, $this->sortType, $this->limit,
                (($this->getTotal() - $this->limit < $this->offset) ? $this->offset : ($this->getTotal() - $this->limit)), $this->embed);
        }

        if ($this->offset > 0) {
            $response[self::PREVIOUS_URL] = $this->getPage($fields, $this->sortBy, $this->sortType, $this->limit,
                ($this->offset - $this->limit) > 0 ? ($this->offset - $this->limit) : 0, $this->embed);

            $response[self::FIRST_URL] = $this->getPage($fields, $this->sortBy, $this->sortType, $this->limit, 0, $this->embed);
        }

        if ($this->limit < $this->getTotal() && !($this->limit + $this->offset >= $this->getTotal())) {
            $response[self::NEXT_URL] = $this->getPage($fields, $this->sortBy, $this->sortType, $this->limit,
                (($this->offset + $this->limit) < $this->getTotal()) ? $this->offset + $this->limit : $this->getTotal() - $this->limit, $this->embed);
        }

        return $response;
    }

    /**
     * @author Rohit Arora
     *
     * @param        $fields
     * @param        $sortBy
     * @param        $sortType
     * @param        $limit
     * @param        $offset
     * @param string $embed
     *
     * @return string
     */
    private function getPage($fields, $sortBy, $sortType, $limit, $offset, $embed = '')
    {
        return \Request::url() . '?fields=' . $fields . '&sort_by=' . $sortBy . '&sort_type=' . $sortType . '&limit=' . $limit . '&offset=' . $offset . '&embed=' . $embed;
    }

    /**
     * @author Rohit Arora
     *
     * @param $by
     *
     * @return bool
     */
    public static function isValidOrderBy($by)
    {
        return $by == static::DEFAULT_SORT_BY;
    }

    /**
     * @author Rohit Arora
     *
     * @param $id
     *
     * @return bool
     */
    public function exists($id)
    {
        return $this->getQueryBuilder()
                    ->find($id)->exist;
    }
}