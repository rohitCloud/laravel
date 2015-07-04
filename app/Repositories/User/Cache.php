<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\User;

use App\Contracts\Repositories\User as UserContract;
use App\Repositories\Repository;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * @author Rohit Arora
 */
class Cache extends Repository implements UserContract
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
     * @param UserContract    $ContractContract
     * @param CacheRepository $Cache
     */
    public function __construct(UserContract $ContractContract, CacheRepository $Cache)
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
        return $this->Cache->remember('users-' . implode('-', $columns), 60, function () use ($columns) {
            return $this->ContractContract->get($columns);
        });
    }
}