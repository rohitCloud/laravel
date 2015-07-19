<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\Comment;

use App\Adapters\Comment as CommentAdapter;
use App\Contracts\Repositories\Comment as CommentContract;
use App\Contracts\Repositories\Post as PostContract;
use App\Exceptions\InvalidData;
use App\Models\Comment as CommentModel;
use App\Models\Post;
use App\Repositories\Base;
use Illuminate\Database\Query\Builder;

/**
 * @author Rohit Arora
 */
class Comment extends Base implements CommentContract
{
    const DEFAULT_OFFSET    = 0;
    const DEFAULT_LIMIT     = 10;
    const DEFAULT_SORT_BY   = CommentModel::ID;
    const DEFAULT_SORT_TYPE = Base::SORT_ASC;
    /**
     * @var PostContract
     */
    private $Post;

    /**
     * @param CommentModel   $Model
     *
     * @param CommentAdapter $Adapter
     * @param PostContract   $Post
     */
    public function __construct(CommentModel $Model, CommentAdapter $Adapter, PostContract $Post)
    {
        parent::__construct($Model, $Adapter);
        $this->Post = $Post;
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return array
     */
    public function fetch($parameters)
    {
        return $this->setRequestParameters($parameters)
                    ->get();
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
        return $this->setRequestParameters($parameters)
                    ->getCommentsRelatedToPost($postID)
                    ->get();
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
        return $this->setQueryBuilder($this->getQueryBuilder()
                                           ->whereHas('post', function ($query) use ($postID) {
                                               /* @var Builder $query */
                                               $query->where(Post::ID, EQUAL, $postID);
                                           }));
    }

    /**
     * @author Rohit Arora
     *
     * @param int   $commentID
     * @param array $parameters
     *
     * @return $this
     */
    public function getByID($commentID, $parameters = [ALL_FIELDS])
    {
        return $this->setRequestParameters($parameters)
                    ->find($commentID);
    }

    /**
     * @author Rohit Arora
     *
     * @param int   $postID
     * @param int   $commentID
     * @param array $parameters
     *
     * @return array
     */
    public function getByPostAndID($postID, $commentID, $parameters = [ALL_FIELDS])
    {
        return $this->setRequestParameters($parameters)
                    ->getCommentsRelatedToPost($postID)
                    ->find($commentID);
    }

    /**
     * @author Rohit Arora
     *
     * @param $by
     *
     * @return bool
     */
    public static function isValidOrderBy($by)
    {
        return CommentModel::isValidOrderBy($by);
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     *
     * @return array
     * @throws InvalidData
     * @throws \App\Exceptions\InvalidArguments
     * @throws \App\Exceptions\InvalidData
     */
    public function store($parameters)
    {
        $this->setPostParameters($parameters);
        if (!$this->Post->exists($this->getData()[CommentModel::POST_ID])) {
            throw new InvalidData("Post does not exists!");
        }

        return $this->save();
    }
}