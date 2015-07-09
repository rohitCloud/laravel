<?php

namespace App\Models\User;

use App\Models\Comment;
use App\Models\Document;
use App\Models\Post;
use App\Models\Role;
use App\Models\User\Details;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
     * @param $by
     *
     * @return bool
     */
    public static function isValidOrderBy($by)
    {
        return in_array($by, [self::ID, self::EMAIL, self::CREATED_AT, self::UPDATED_AT]);
    }

    /**
     * @author Rohit Arora
     *
     * @return HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @author Rohit Arora
     *
     * @return HasOne
     */
    public function details()
    {
        return $this->hasOne(Details::class);
    }

    /**
     * @author Rohit Arora
     *
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @author Rohit Arora
     *
     * @return HasManyThrough
     */
    public function comments()
    {
        return $this->hasManyThrough(Comment::class, Post::class);
    }

    /**
     * @author Rohit Arora
     *
     * @return MorphMany
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'parent');
    }
}
