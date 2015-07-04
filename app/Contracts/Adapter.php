<?php
/**
 * @author Rohit Arora
 */
namespace App\Contracts;


/**
 * @author  Rohit Arora
 *
 * Class Post
 * @package App\Adapters
 */
interface Adapter
{
    /**
     * @author Rohit Arora
     *
     * @param $columns
     */
    public function clean($columns);

    /**
     * @author Rohit Arora
     *
     * @param string $key
     *
     * @return string
     */
    public function keyExists($key);

    /**
     * @author Rohit Arora
     *
     * @param $fields
     * @param $list
     *
     * @return array
     */
    public function reFilter($fields, $list);

    /**
     * @author Rohit Arora
     *
     * @param array $fields
     *
     * @return array
     */
    public function filter($fields = ['*']);
}