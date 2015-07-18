<?php
/**
 * @author Rohit Arora
 */
namespace App\Adapters;

use App\Contracts\Adapter;
use App\Repositories\Base as BaseRepository;

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
                $returnData[$key] = $this->typeCast($data[$binding[self::PROPERTY]], $binding[self::DATA_TYPE]);
            } else if (isset($binding[self::CALLBACK]) && in_array($key, $embed)) {
                if (isset($data[$binding[self::CALLBACK][self::PROPERTY]])) {
                    $embedData = call_user_func_array([\App::make($binding[self::CALLBACK][CALLBACK_CLASS]), $binding[self::CALLBACK][CALLBACK_FUNCTION]],
                        [$data[$binding[self::CALLBACK][self::PROPERTY]], [BaseRepository::EMBED => implode(COMMA, $embed)]]);
                    if ($embedData && isset($embedData[Response::DATA])) {
                        $returnData[$key] = $embedData[Response::DATA];
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
    public function getModelFields($fields = [ALL_FIELDS])
    {
        $modelFields = [];
        foreach ($this->getBindings() as $key => $binding) {
            if (isset($binding[self::PROPERTY]) && (in_array($key, $fields) || (in_array(ALL_FIELDS, $fields)))) {
                $modelFields[$key] = $binding[self::PROPERTY];
            }
        }

        return $modelFields;
    }

    /**
     * @author Rohit Arora
     *
     * @param array $fields
     *
     * @return array
     */
    public function getModelFieldsWithData($fields)
    {
        $modelFields = [];
        foreach ($this->getBindings() as $key => $binding) {
            if (isset($binding[self::PROPERTY]) && (in_array($key, array_keys($fields)))) {
                $modelFields[$key] = [$binding[self::PROPERTY] => $this->typeCast($fields[$key], $binding[self::DATA_TYPE])];
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

    /**
     * @author Rohit Arora
     *
     * @param $data
     * @param $type
     *
     * @return mixed
     */
    private function typeCast($data, $type)
    {
        if ($type == self::TYPE_BOOLEAN) {
            return (bool) $data;
        } else if ($type == self::TYPE_INTEGER) {
            return (int) $data;
        } else if ($type == self::TYPE_STRING) {
            return (string) $data;
        } else if ($type == self::TYPE_DATETIME) {
            return $data;
        }

        return $data;
    }
}