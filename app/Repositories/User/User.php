<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\User;

use App\Adapters\User as UserAdapter;
use App\Contracts\Repositories\User as UserContract;
use App\Models\User as UserModel;
use App\Repositories\Repository;

/**
 * @author Rohit Arora
 */
class User extends Repository implements UserContract
{
    /**
     * @param UserModel   $Model
     *
     * @param UserAdapter $Adapter
     *
     * @internal param UserModel $User
     */
    public function __construct(UserModel $Model, UserAdapter $Adapter)
    {
        parent::__construct($Model, $Adapter);
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return array
     */
    public function get($parameters)
    {
        $parameters = $this->Adapter->filter(isset($parameters['fields']) ? explode(',', $parameters['fields']) : ['*']);

        if (!$parameters) {
            return [];
        }

        return $this->Adapter->reFilter($parameters, $this->fetch($parameters)
                                                          ->toArray());
    }
}