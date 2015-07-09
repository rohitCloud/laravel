<?php

namespace App\Models\Tag;

use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @author  Rohit Arora
 *
 * Class Tag
 * @package App\Models
 */
class Tag extends Model
{
    const TABLE = 'tags';

    const ID         = 'id';
    const NAME       = 'name';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = self::TABLE;

    /**
     * @author Rohit Arora
     *
     * @return MorphToMany
     */
    public function posts()
    {
        return $this->morphedByMany(Post::class, 'taggables');
    }
}
