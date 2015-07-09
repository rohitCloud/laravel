<?php
/**
 * @author Rohit Arora
 */

namespace App\Adapters;

use App\Contracts\Adapter as AdapterContract;
use App\Models\Comment as CommentModel;

/**
 * @author  Rohit Arora
 *
 * Class Comment
 * @package App\Adapters
 */
class Comment extends Base implements AdapterContract
{
    const ID         = 'id';
    const COMMENT    = 'comment';
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
            $this->keyExists(self::ID)         => CommentModel::ID,
            $this->keyExists(self::COMMENT)    => CommentModel::COMMENT,
            $this->keyExists(self::POST)       => CommentModel::POST_ID,
            $this->keyExists(self::CREATED_AT) => CommentModel::CREATED_AT,
            $this->keyExists(self::UPDATED_AT) => CommentModel::UPDATED_AT,
        ]);
    }
}