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
    public function getValidations();

    /**
     * @author Rohit Arora
     *
     * @return array
     */
    public function getBindings();

    /**
     * @author Rohit Arora
     *
     * @param array $fields
     * @param array $data
     * @param bool  $single
     * @param array $embed
     *
     * @return mixed
     */
    public function reFilter($fields, $data, $single, $embed = []);

    /**
     * @author Rohit Arora
     *
     * @param array $fields
     *
     * @return array
     */
    public function getModelFields($fields = [ALL_FIELDS]);

    /**
     * @author Rohit Arora
     *
     * @param array $fields
     *
     * @return array
     */
    public function getModelFieldsWithData($fields);

    /**
     * @author Rohit Arora
     *
     * @param $modelFields
     *
     * @return array
     */
    public function getFilteredFields($modelFields);

    /**
     * @author Rohit Arora
     *
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public function validator(array $data);
}