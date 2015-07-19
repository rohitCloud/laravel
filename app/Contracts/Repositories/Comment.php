<?php
/**
 * @author Rohit Arora
 */
namespace App\Contracts\Repositories;

/**
 * @author Rohit Arora
 */
interface Comment
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
     * @param $postID
     *
     * @return array
     */
    public function getCommentsByPost($parameters, $postID);

    /**
     * @author Rohit Arora
     *
     * @param int   $commentID
     * @param array $parameters
     *
     * @return array
     */
    public function getByID($commentID, $parameters = [ALL_FIELDS]);

    /**
     * @author Rohit Arora
     *
     * @param int   $postID
     * @param int   $commentID
     * @param array $parameters
     *
     * @return array
     */
    public function getByPostAndID($postID, $commentID, $parameters = [ALL_FIELDS]);

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

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return array
     */
    public function store($parameters);
}