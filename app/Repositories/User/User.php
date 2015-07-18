<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\User;

use App\Adapters\User as UserAdapter;
use App\Contracts\Repositories\User as UserContract;
use App\Exceptions\InvalidArguments;
use App\Models\User\User as UserModel;
use App\Repositories\Base;

/**
 * @author Rohit Arora
 */
class User extends Base implements UserContract
{
    const DEFAULT_OFFSET    = 0;
    const DEFAULT_LIMIT     = 10;
    const DEFAULT_SORT_BY   = UserModel::ID;
    const DEFAULT_SORT_TYPE = Base::SORT_ASC;

    /**
     * @param UserModel   $Model
     *
     * @param UserAdapter $Adapter
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
    public function fetch($parameters)
    {
        return $this->setRequestParameters($parameters)
                    ->get();
    }

    /**
     * @author Rohit Arora
     *
     * @param int   $userID
     * @param array $parameters
     *
     * @return User
     */
    public function getByID($userID, $parameters = [ALL_FIELDS])
    {
        return $this->setRequestParameters($parameters)
                    ->find($userID);
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
        return UserModel::isValidOrderBy($by);
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return array
     * @throws InvalidArguments
     */
    public function store($parameters)
    {
        $this->setPostParameters($parameters);

        $data = $this->getData();
        if ($this->exists([UserModel::EMAIL => $data[UserModel::EMAIL]])) {
            throw new InvalidArguments('Email Already exists');
        }

        $data[UserModel::PASSWORD] = bcrypt($data[UserModel::PASSWORD]);
        $this->setData($data);

        return $this->save();
    }
}