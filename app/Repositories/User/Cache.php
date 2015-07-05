<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\User;

use App\Contracts\Repositories\User as UserContract;
use App\Repositories\Base;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * @author Rohit Arora
 */
class Cache extends Base implements UserContract
{
    /**
     * @var Base
     */
    private $UserContract;

    /**
     * @var CacheRepository
     */
    private $Cache;

    /**
     * @param UserContract    $UserContract
     * @param CacheRepository $Cache
     */
    public function __construct(UserContract $UserContract, CacheRepository $Cache)
    {
        $this->UserContract = $UserContract;
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
        return $this->Cache->remember('users-' . implode('-', $columns), 60, function () use ($columns) {
            return $this->UserContract->get($columns);
        });
    }
}