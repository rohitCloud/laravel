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
     * @param $condition
     *
     * @return mixed
     */
    public function exists($condition);

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
     * @param $parameters
     * @param $commentID
     *
     * @return array
     */
    public function modify($parameters, $commentID);

    /**
     * @author Rohit Arora
     *
     * @param int|array $commentID
     *
     * @return mixed
     */
    public function destroy($commentID);

    /**
     * @author Rohit Arora
     *
     * @param $postID
     *
     * @return mixed
     */
    public function destroyByPost($postID);
}