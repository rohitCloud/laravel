<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @author  Rohit Arora
 *
 * Class Taggable
 * @package App\Models
 */
class Taggable extends Model
{
    const TABLE = 'taggables';

    const ID          = 'id';
    const TAG_ID      = 'tag_id';
    const PARENT_ID   = 'parent_id';
    const PARENT_TYPE = 'parent_type';
    const CREATED_AT  = 'created_at';
    const UPDATED_AT  = 'updated_at';

    protected $table = self::TABLE;
}
