<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @author  Rohit Arora
 *
 * Class UserDetail
 * @package App\Models
 */
class UserDetail extends Model
{
    const TABLE = 'user_details';

    const ID         = 'id';
    const USER_ID    = 'user_id';
    const PHONE      = 'phone';
    const ADDRESS    = 'address';
    const CITY       = 'city';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = self::TABLE;

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
