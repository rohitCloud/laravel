<?php
/**
 * @author Rohit Arora
 */
namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

/**
 * @author Rohit Arora
 */
interface Repository
{
    /**
     * @author Rohit Arora
     *
     * @param array $parameters
     *
     * @return Collection
     */
    public function get($parameters);
}