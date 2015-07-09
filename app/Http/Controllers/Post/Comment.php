<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Contracts\Repositories\Comment as CommentContract;

/**
 * @author  Rohit Arora
 *
 * Class Comment
 * @package App\Http\Controllers
 */
class Comment extends Controller
{
    /**
     * @var CommentContract
     */
    private $CommentContract;

    /**
     * @param CommentContract $CommentContract
     */
    public function __construct(CommentContract $CommentContract)
    {
        $this->setCommentContract($CommentContract);
    }

    /**
     * @author Rohit Arora
     *
     * @return CommentContract
     */
    public function getCommentContract()
    {
        return $this->CommentContract;
    }

    /**
     * @author Rohit Arora
     *
     * @param CommentContract $CommentContract
     */
    public function setCommentContract($CommentContract)
    {
        $this->CommentContract = $CommentContract;
    }

    /**
     * @author Rohit Arora
     *
     * @param $postID
     *
     * @return mixed
     */
    public function index($postID)
    {
        return $this->getCommentContract()
                    ->getCommentsByPost($this->inputFilter(), $postID);
    }
}
