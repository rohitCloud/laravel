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
        parent::__construct();
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
        try {
            $comments = $this->getCommentContract()
                             ->getCommentsByPost($this->inputs(), $postID);
        } catch (\Exception $Exception) {
            return $this->responseAdapter->responseWithException($Exception);
        }

        return $this->responseAdapter->response($comments);
    }

    /**
     * @author Rohit Arora
     *
     * @param $postID
     * @param $id
     *
     * @return mixed
     */
    public function show($postID, $id)
    {
        try {
            $comment = $this->getCommentContract()
                            ->getByPostAndID($postID, $id, $this->inputs());
        } catch (\Exception $Exception) {
            return $this->responseAdapter->responseWithException($Exception);
        }

        return $this->responseAdapter->response($comment);
    }
}
