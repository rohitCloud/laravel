<?php
/**
 * @author Rohit Arora
 */

namespace App\Adapters;

use App\Contracts\Adapter as AdapterContract;
use App\Models\User\User as UserModel;

/**
 * @author  Rohit Arora
 *
 * Class User
 * @package App\Adapters
 */
class User extends Base implements AdapterContract
{
    const ID               = 'id';
    const EMAIL            = 'email';
    const NAME             = 'name';
    const PASSWORD         = 'password';
    const CONFIRM_PASSWORD = 'confirm_password';
    const CREATED_AT       = 'created_at';
    const UPDATED_AT       = 'updated_at';

    protected $validations = [
        self::EMAIL    => 'required|string|email',
        self::NAME     => 'required|string|min:3',
        self::PASSWORD => 'required|string|min:6|alpha_num'
    ];

    /**
     * @author Rohit Arora
     *
     * @return array
     */
    public function getBindings()
    {
        return [
            self::ID         => [self::PROPERTY  => UserModel::ID,
                                 self::DATA_TYPE => self::TYPE_INTEGER],
            self::NAME       => [self::PROPERTY  => UserModel::NAME,
                                 self::DATA_TYPE => self::TYPE_STRING],
            self::EMAIL      => [self::PROPERTY  => UserModel::EMAIL,
                                 self::DATA_TYPE => self::TYPE_STRING],
            self::PASSWORD   => [self::PROPERTY  => UserModel::PASSWORD,
                                 self::DATA_TYPE => self::TYPE_STRING],
            self::CREATED_AT => [self::PROPERTY  => UserModel::CREATED_AT,
                                 self::DATA_TYPE => self::TYPE_DATETIME],
            self::UPDATED_AT => [self::PROPERTY  => UserModel::UPDATED_AT,
                                 self::DATA_TYPE => self::TYPE_DATETIME]
        ];
    }

    /**
     * @author Rohit Arora
     *
     * @return array
     */
    public function getValidations()
    {
        return $this->validations;
    }
}