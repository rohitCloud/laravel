<?php
/**
 * @author Rohit Arora
 */
namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;

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
     * @return Collection
     */
    public function fetch($parameters);

    /**
     * @author Rohit Arora
     *
     * @param $userID
     *
     * @return Post
     */
    public function getByID($userID);
}