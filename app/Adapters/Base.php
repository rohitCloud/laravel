<?php
/**
 * @author Rohit Arora
 */
namespace App\Adapters;

use App\Contracts\Adapter;

/**
 * @author  Rohit Arora
 *
 * Class Post
 * @package App\Adapters
 */
abstract class Base implements Adapter
{
    const PROPERTY  = 'property';
    const DATA_TYPE = 'data_type';
    const CALLBACK  = 'callback';

    const TYPE_INTEGER  = 'integer';
    const TYPE_STRING   = 'string';
    const TYPE_BOOLEAN  = 'boolean';
    const TYPE_RESOURCE = 'resource';
    const TYPE_DATETIME = 'datetime';

    /**
     * @author Rohit Arora
     *
     * @param $fields
     * @param $list
     *
     * @return array
     */
    public function reFilter($fields, $list)
    {
        if (!$fields || !$list) {
            return false;
        }

        $returnData = $output = [];

        foreach ($list as $value) {
            foreach ($this->getBindings() as $key => $binding) {
                if (isset($binding[self::PROPERTY]) && in_array($key, $fields)) {
                    $returnData[$key] = $value[$binding[self::PROPERTY]];
                } else if (isset($binding[self::CALLBACK])) {
                    $returnData[$key] = call_user_func([\App::make($binding[self::CALLBACK]['class']),
                                                        $binding[self::CALLBACK]['function']], $value[$binding[self::CALLBACK][self::PROPERTY]]);
                }
            }
            $output[] = $returnData;
        }

        return $output;
    }

    /**
     * @author Rohit Arora
     *
     * @param array $fields
     *
     * @return array
     */
    public function getModelFields($fields = ['*'])
    {
        $modelFields = [];
        foreach ($this->getBindings() as $key => $binding) {
            if (isset($binding[self::PROPERTY]) && (in_array($key, $fields) || (in_array('*', $fields)))) {
                $modelFields[$key] = $binding[self::PROPERTY];
            }
        }

        return $modelFields;
    }

    /**
     * @author Rohit Arora
     *
     * @param $modelFields
     *
     * @return array
     */
    public function getFilteredFields($modelFields)
    {
        $filterFields = [];
        foreach ($this->getBindings() as $key => $binding) {
            if (isset($binding[self::PROPERTY]) && in_array($binding[self::PROPERTY], $modelFields)) {
                $filterFields[] = $key;
            }
        }

        return $filterFields;
    }
}