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
}