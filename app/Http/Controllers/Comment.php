<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Contracts\Repositories\Comment as CommentContract;
use Illuminate\Http\Response;

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
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $comments = $this->getCommentContract()
                         ->fetch($this->inputFilter());

        return $this->responseAdapter->response($comments);
    }

    /**
     * @author Rohit Arora
     *
     * @param $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $comment = $this->getCommentContract()
                        ->getByID($id, $this->inputFilter());

        return $this->responseAdapter->response($comment);
    }
}
