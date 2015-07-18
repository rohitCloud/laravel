<?php
/**
 * @author Rohit Arora
 */
namespace App\Contracts\Repositories;

/**
 * @author Rohit Arora
 */
interface User
{
    /**
     * @author Rohit Arora
     *
     * @param array $parameters
     *
     * @return array
     */
    public function fetch($parameters);

    /**
     * @author Rohit Arora
     *
     * @param int   $userID
     * @param array $parameters
     *
     * @return array
     */
    public function getByID($userID, $parameters = [ALL_FIELDS]);

    /**
     * @author Rohit Arora
     *
     * @param $by
     *
     * @return bool
     */
    public static function isValidOrderBy($by);

    /**
     * @author Rohit Arora
     *
     * @param int $id
     *
     * @return bool
     */
    public function exists($id);
}