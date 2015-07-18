<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @author  Rohit Arora
 *
 * Class Comment
 * @package App
 */
class Comment extends Model
{
    const TABLE = 'comments';

    const ID         = 'id';
    const COMMENT    = 'comment';
    const POST_ID    = 'post_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = self::TABLE;

    protected $fillable = [self::COMMENT, self::POST_ID];

    /**
     * @author Rohit Arora
     *
     * @param $by
     *
     * @return bool
     */
    public static function isValidOrderBy($by)
    {
        return in_array($by, [self::ID, self::COMMENT, self::CREATED_AT, self::UPDATED_AT]);
    }

    /**
     * @author Rohit Arora
     *
     * @return BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
