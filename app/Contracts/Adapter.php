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
     * @return array
     */
    public function getBindings();

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
    public function getModelFields($fields = ['*']);

    /**
     * @author Rohit Arora
     *
     * @param $modelFields
     *
     * @return array
     */
    public function getFilteredFields($modelFields);
}