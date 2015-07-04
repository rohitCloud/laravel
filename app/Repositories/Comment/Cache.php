<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\Comment;

use App\Contracts\Repositories\Comment as CommentContract;
use App\Repositories\Repository;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * @author Rohit Arora
 */
class Cache extends Repository implements CommentContract
{
    /**
     * @var Repository
     */
    private $ContractContract;

    /**
     * @var CacheRepository
     */
    private $Cache;

    /**
     * @param CommentContract $ContractContract
     * @param CacheRepository $Cache
     */
    public function __construct(CommentContract $ContractContract, CacheRepository $Cache)
    {
        $this->ContractContract = $ContractContract;
        $this->Cache            = $Cache;
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
        return $this->Cache->remember('comment-' . implode('-', $columns), 60, function () use ($columns) {
            return $this->ContractContract->get($columns);
        });
    }
}