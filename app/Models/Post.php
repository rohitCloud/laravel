<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @author  Rohit Arora
 *
 * Class Post
 * @package App\Models
 */
class Post extends Model
{
    const TABLE = 'posts';

    const ID         = 'id';
    const USER_ID    = 'user_id';
    const TITLE      = 'title';
    const BODY       = 'body';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = self::TABLE;

    /**
     * @author Rohit Arora
     *
     * @return HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @author Rohit Arora
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
