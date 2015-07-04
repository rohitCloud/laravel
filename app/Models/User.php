<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @author  Rohit Arora
 *
 * Class User
 * @package App
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    const TABLE = 'users';

    const ID             = 'id';
    const NAME           = 'name';
    const EMAIL          = 'email';
    const PASSWORD       = 'password';
    const CREATED_AT     = 'created_at';
    const UPDATED_AT     = 'updated_at';
    const REMEMBER_TOKEN = 'remember_token';

    use Authenticatable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = self::TABLE;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [self::NAME, self::EMAIL, self::PASSWORD];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [self::PASSWORD, self::REMEMBER_TOKEN];

    /**
     * @author Rohit Arora
     *
     * @return HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
