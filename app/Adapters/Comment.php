<?php
/**
 * @author Rohit Arora
 */

namespace App\Adapters;

use App\Contracts\Adapter as AdapterContract;
use App\Models\Comment as CommentModel;
use App\Repositories\Post\Post;

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
    const POST_ID    = 'post_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $postValidations = [
        self::COMMENT => 'required|string|min:10',
        self::POST_ID => 'required|numeric'
    ];

    protected $putValidations = [
        self::COMMENT => 'string|min:10'
    ];

    /**
     * @author Rohit Arora
     *
     * @return array
     */
    public function getValidations()
    {
        return $this->{strtolower(\Request::method()) . 'Validations'};
    }

    /**
     * @author Rohit Arora
     *
     * @return array
     */
    public function getBindings()
    {
        return [
            self::ID         => [self::PROPERTY  => CommentModel::ID,
                                 self::DATA_TYPE => self::TYPE_INTEGER],
            self::COMMENT    => [self::PROPERTY  => CommentModel::COMMENT,
                                 self::DATA_TYPE => self::TYPE_STRING],
            self::POST_ID    => [self::PROPERTY  => CommentModel::POST_ID,
                                 self::DATA_TYPE => self::TYPE_INTEGER],
            self::POST       => [self::DATA_TYPE => self::TYPE_RESOURCE,
                                 self::CALLBACK  => [CALLBACK_CLASS    => Post::class,
                                                     CALLBACK_FUNCTION => 'getByID',
                                                     self::PROPERTY    => CommentModel::POST_ID]],
            self::CREATED_AT => [self::PROPERTY  => CommentModel::CREATED_AT,
                                 self::DATA_TYPE => self::TYPE_DATETIME],
            self::UPDATED_AT => [self::PROPERTY  => CommentModel::UPDATED_AT,
                                 self::DATA_TYPE => self::TYPE_DATETIME]
        ];
    }
}