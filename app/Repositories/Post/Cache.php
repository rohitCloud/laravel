<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\Post;

use App\Contracts\Repository as RepositoryContract;
use App\Repositories\Repository;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * @author Rohit Arora
 */
class Cache extends Repository implements RepositoryContract
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
     * @param RepositoryContract $ContractContract
     * @param CacheRepository    $Cache
     */
    public function __construct(RepositoryContract $ContractContract, CacheRepository $Cache)
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
    public function fetch($columns = ['*'])
    {
        return $this->Cache->remember('posts-' . implode('-', $columns), 60, function () use ($columns) {
            return $this->ContractContract->get($columns);
        });
    }
}