<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\Comment;

use App\Adapters\Comment as CommentAdapter;
use App\Contracts\Repositories\Comment as CommentContract;
use App\Models\Comment as CommentModel;
use App\Models\Post;
use App\Repositories\Base;
use Illuminate\Database\Query\Builder;

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
                    ->setFields()
                    ->setDataFromModel()
                    ->process();
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     * @param $postID
     *
     * @return Comment
     */
    public function getCommentsByPost($parameters, $postID)
    {
        return $this->setParameters($parameters)
                    ->getCommentsRelatedToPost($postID);
    }

    /**
     * @author Rohit Arora
     *
     * @param $postID
     *
     * @return Comment
     */
    public function getCommentsRelatedToPost($postID)
    {
        if (!Post::find($postID)) {
            return [];
        }

        return $this->setQueryBuilder($this->getQueryBuilder()
                                           ->whereHas('post', function ($query) use ($postID) {
                                               /* @var Builder $query */
                                               $query->where(Post::ID, '=', $postID);
                                           }))
                    ->setFields()
                    ->setDataFromModel()
                    ->process();
    }
}