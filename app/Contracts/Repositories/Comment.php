<?php
/**
 * @author Rohit Arora
 */
namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;

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
     * @return Collection
     */
    public function fetch($parameters);

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     * @param $postID
     *
     * @return mixed
     */
    public function getCommentsByPost($parameters, $postID);

    /**
     * @author Rohit Arora
     *
     * @param int   $commentID
     * @param array $parameters
     *
     * @return Comment
     */
    public function getByID($commentID, $parameters = ['*']);
}