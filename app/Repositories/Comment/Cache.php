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
class Cache extends Base implements CommentContract
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
    public function get($columns = ['*'])
    {
        return $this->Cache->remember('comment-' . implode('-', $columns), 60, function () use ($columns) {
            return $this->CommentContract->get($columns);
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
     * @param $userID
     *
     * @return mixed
     */
    public function getByID($userID)
    {
        // TODO: Implement getByID() method.
    }
}