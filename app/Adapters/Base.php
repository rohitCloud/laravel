<?php
/**
 * @author Rohit Arora
 */
namespace App\Adapters;

/**
 * @author  Rohit Arora
 *
 * Class Post
 * @package App\Adapters
 */
abstract class Base
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
     * @var array
     */
    protected $fields;

    /**
     * @author Rohit Arora
     *
     * @param $columns
     */
    public function clean($columns)
    {
        if (isset($columns[0])) {
            unset($columns[0]);
        }

        return $columns;
    }

    /**
     * @author Rohit Arora
     *
     * @param string $key
     *
     * @return string
     */
    public function keyExists($key)
    {
        if (in_array('*', $this->fields)) {
            return $key;
        }

        return in_array($key, $this->fields) ? $key : 0;
    }

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

        $fields = array_flip($fields);

        $returnData = $output = [];
        foreach ($list as $value) {
            foreach ($fields as $key => $fieldValue) {
                $returnData[$fieldValue] = $value[$key];
            }
            $output[] = $returnData;
        }

        return $output;
    }
}