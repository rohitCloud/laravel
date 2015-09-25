<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\Post;

use App\Contracts\Repositories\Post as PostContract;
use App\Repositories\Base;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * @author Rohit Arora
 */
class Cache implements PostContract
{
    /**
     * @var Base
     */
    private $PostContract;

    /**
     * @var CacheRepository
     */
    private $Cache;

    /**
     * @param PostContract    $PostContract
     * @param CacheRepository $Cache
     */
    public function __construct(PostContract $PostContract, CacheRepository $Cache)
    {
        $this->PostContract = $PostContract;
        $this->Cache        = $Cache;
    }

    /**
     * @author Rohit Arora
     *
     * @param array $columns
     *
     * @return Collection
     */
    public function fetch($columns = [ALL_FIELDS])
    {
        return $this->Cache->remember('posts-' . implode('-', $columns), 60, function () use ($columns) {
            return $this->PostContract->fetch($columns);
        });
    }

    /**
     * @author Rohit Arora
     *
     * @param int   $postID
     * @param array $parameters
     *
     * @return PostContract
     */
    public function getByID($postID, $parameters = [ALL_FIELDS])
    {
        return true;
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return array
     */
    public function store($parameters)
    {
        return true;
    }

    /**
     * @author Rohit Arora
     *
     * @param $by
     *
     * @return bool
     */
    public static function isValidOrderBy($by)
    {
        return true;
    }

    /**
     * @author Rohit Arora
     *
     * @param array $parameters
     * @param int   $userID
     *
     * @return array
     */
    public function getPostsByUser($parameters, $userID)
    {
        return true;
    }

    /**
     * @author Rohit Arora
     *
     * @param int   $userID
     * @param int   $postID
     * @param array $parameters
     *
     * @return array
     */
    public function getByUserAndID($userID, $postID, $parameters = [ALL_FIELDS])
    {
        return true;
    }

    /**
     * @author Rohit Arora
     *
     * @param int $id
     *
     * @return bool
     */
    public function exists($id)
    {
        return true;
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     * @param $postID
     *
     * @return array
     */
    public function modify($parameters, $postID)
    {
        return true;
    }

    /**
     * @author Rohit Arora
     *
     * @param int|array $postID
     *
     * @return mixed
     */
    public function destroy($postID)
    {
        return true;
    }
}
