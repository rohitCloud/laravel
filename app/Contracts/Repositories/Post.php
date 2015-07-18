<?php
/**
 * @author Rohit Arora
 */
namespace App\Contracts\Repositories;

/**
 * @author Rohit Arora
 */
interface Post
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
     * @param $parameters
     *
     * @return array
     */
    public function store($parameters);

    /**
     * @author Rohit Arora
     *
     * @param int   $postID
     * @param array $parameters
     *
     * @return array
     */
    public function getByID($postID, $parameters = [ALL_FIELDS]);

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
     * @param array $parameters
     * @param int   $userID
     *
     * @return array
     */
    public function getPostsByUser($parameters, $userID);

    /**
     * @author Rohit Arora
     *
     * @param int   $userID
     * @param int   $postID
     * @param array $parameters
     *
     * @return array
     */
    public function getByUserAndID($userID, $postID, $parameters = [ALL_FIELDS]);

}