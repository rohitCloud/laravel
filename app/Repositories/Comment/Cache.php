<?php

/**
 * @author Rohit Arora
 */

namespace App\Repositories\Comment;

use App\Contracts\Repositories\Comment as CommentContract;
use App\Repositories\Base;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * @author Rohit Arora
 */
class Cache implements CommentContract
{
    /**
     * @var Base
     */
    private $CommentContract;

    /**
     * @var CacheRepository
     */
    private $Cache;

    /**
     * @param CommentContract $CommentContract
     * @param CacheRepository $Cache
     */
    public function __construct(CommentContract $CommentContract, CacheRepository $Cache)
    {
        $this->CommentContract = $CommentContract;
        $this->Cache           = $Cache;
    }

    /**
     * @author Rohit Arora
     *
     * @param array $columns
     *
     * @return Collection
     */
    public function fetch($columns = [ALL_FIELDS])
    {
        return $this->Cache->remember('comment-' . implode('-', $columns), 60, function () use ($columns) {
            return $this->CommentContract->fetch($columns);
        });
    }

    /**
     * @author Rohit Arora
     *
     * @param $parameters
     * @param $postID
     *
     * @return mixed
     */
    public function getCommentsByPost($parameters, $postID)
    {
        // TODO: Implement getCommentsByPost() method.
    }

    /**
     * @author Rohit Arora
     *
     * @param int   $commentID
     * @param array $parameters
     *
     * @return CommentContract
     */
    public function getByID($commentID, $parameters = [ALL_FIELDS])
    {
        // TODO: Implement getByID() method.
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
        // TODO: Implement getByPostAndID() method.
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
        // TODO: Implement isValidOrderBy() method.
    }
}