<?php
/**
 * @author Rohit Arora
 */

namespace App\Adapters;

use App\Contracts\Adapter as AdapterContract;
use App\Models\Post;
use App\Models\User as UserModel;

/**
 * @author  Rohit Arora
 *
 * Class User
 * @package App\Adapters
 */
class User extends Base implements AdapterContract
{
    const ID         = 'id';
    const EMAIL      = 'email';
    const NAME       = 'name';
    const POST       = 'post';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * @author Rohit Arora
     *
     * @param array $fields
     *
     * @return array
     */
    public function filter($fields = ['*'])
    {
        $this->fields = $fields;

        return $this->clean([
            $this->keyExists(self::ID)         => UserModel::ID,
            $this->keyExists(self::NAME)       => UserModel::NAME,
            $this->keyExists(self::EMAIL)      => UserModel::EMAIL,
            $this->keyExists(self::CREATED_AT) => UserModel::CREATED_AT,
            $this->keyExists(self::UPDATED_AT) => UserModel::UPDATED_AT,
        ]);
    }
}