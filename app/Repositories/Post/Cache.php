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
class Cache extends Base implements PostContract
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
    public function get($columns = ['*'])
    {
        return $this->Cache->remember('posts-' . implode('-', $columns), 60, function () use ($columns) {
            return $this->PostContract->get($columns);
        });
    }

    /**
     * @author Rohit Arora
     *
     * @param $userID
     *
     * @return mixed
     */
    public function getByID($userID)
    {
        // TODO: Implement getByID() method.
    }
}