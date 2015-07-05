<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @author  Rohit Arora
 *
 * Class Document
 * @package App\Models
 */
class Document extends Model
{
    const TABLE = 'documents';

    const ID          = 'id';
    const PARENT_ID   = 'parent_id';
    const PATH        = 'path';
    const PARENT_TYPE = 'parent_type';
    const CREATED_AT  = 'created_at';
    const UPDATED_AT  = 'updated_at';

    protected $table = self::TABLE;

    /**
     * @author Rohit Arora
     *
     * @return MorphTo
     */
    public function parent()
    {
        return $this->morphTo();
    }
}
