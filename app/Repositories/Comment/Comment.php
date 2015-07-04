<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\Comment;

use App\Adapters\Comment as CommentAdapter;
use App\Contracts\Repositories\Comment as CommentContract;
use App\Models\Comment as CommentModel;
use App\Repositories\Repository;

/**
 * @author Rohit Arora
 */
class Comment extends Repository implements CommentContract
{
    const OFFSET = 0;
    const LIMIT  = 10;

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
        $fields = $this->getFields($parameters);

        if (!$fields) {
            return [];
        }

        $commentList = $this->bindOffsetLimit($parameters)
                            ->fetch($fields)
                            ->toArray();

        return $this->bindData($fields, $commentList);
    }
}