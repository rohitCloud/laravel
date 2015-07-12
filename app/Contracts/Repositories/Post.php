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
     * @param int   $postID
     * @param array $parameters
     *
     * @return array
     */
    public function getByID($postID, $parameters = [ALL_FIELDS]);
}