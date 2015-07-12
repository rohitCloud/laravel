<?php
/**
 * @author Rohit Arora
 */
namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;

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
     * @return Collection
     */
    public function fetch($parameters);

    /**
     * @author Rohit Arora
     *
     * @param int   $postID
     * @param array $parameters
     *
     * @return Post
     */
    public function getByID($postID, $parameters = ['*']);
}