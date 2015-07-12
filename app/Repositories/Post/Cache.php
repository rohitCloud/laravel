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
    public function fetch($columns = ['*'])
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
    public function getByID($postID, $parameters = ['*'])
    {
        // TODO: Implement getByID() method.
    }
}