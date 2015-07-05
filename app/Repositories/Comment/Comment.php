<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\Comment;

use App\Adapters\Comment as CommentAdapter;
use App\Contracts\Repositories\Comment as CommentContract;
use App\Models\Comment as CommentModel;
use App\Repositories\Base;

/**
 * @author Rohit Arora
 */
class Comment extends Base implements CommentContract
{
    const OFFSET    = 0;
    const LIMIT     = 10;
    const SORT_BY   = CommentModel::ID;
    const SORT_TYPE = Base::SORT_ASC;

    /**
     * @param CommentModel   $Model
     *
     * @param CommentAdapter $Adapter
     *
     * @internal param CommentModel $Comment
     */
    public function __construct(CommentModel $Model, CommentAdapter $Adapter)
    {
        parent::__construct($Model, $Adapter);
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return array
     */
    public function get($parameters)
    {
        return $this->setParameters($parameters)
                    ->process();
    }
}