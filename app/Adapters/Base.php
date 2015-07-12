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
     * @param array $fields
     * @param array $data
     * @param bool  $single
     * @param array $embed
     *
     * @return array|bool
     */
    public function reFilter($fields, $data, $single, $embed = [])
    {
        if (!$fields || !$data) {
            return false;
        }

        if ($single) {
            return $this->generate($data, $fields, $embed);
        }

        $output = [];

        foreach ($data as $value) {
            $output[] = $this->generate($value, $fields, $embed);
        }

        return $output;
    }

    /**
     * @author Rohit Arora
     *
     * @param $data
     * @param $fields
     * @param $embed
     *
     * @return array
     */
    private function generate($data, $fields, $embed)
    {
        $returnData = [];

        foreach ($this->getBindings() as $key => $binding) {
            if (isset($binding[self::PROPERTY]) && in_array($key, $fields)) {
                $returnData[$key] = $data[$binding[self::PROPERTY]];
            } else if (isset($binding[self::CALLBACK]) && in_array($key, $embed)) {
                if (isset($data[$binding[self::CALLBACK][self::PROPERTY]])) {
                    $embedData = call_user_func([\App::make($binding[self::CALLBACK]['class']),
                                                 $binding[self::CALLBACK]['function']], $data[$binding[self::CALLBACK][self::PROPERTY]]);

                    if ($embedData && isset($embedData['data'])) {
                        $returnData[$key] = $embedData['data'];
                    }
                }
            }
        }

        return $returnData;
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