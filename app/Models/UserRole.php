<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @author  Rohit Arora
 *
 * Class UserRole
 * @package App\Models
 */
class UserRole extends Model
{
    const TABLE = 'user_roles';

    const ID         = 'id';
    const USER_ID    = 'user_id';
    const ROLE_ID    = 'role_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = self::TABLE;
}
