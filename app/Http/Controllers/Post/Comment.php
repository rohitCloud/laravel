<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Contracts\Repositories\Post as PostContract;

/**
 * @author  Rohit Arora
 *
 * Class Comment
 * @package App\Http\Controllers
 */
class Comment extends Controller
{
    /**
     * @var PostContract
     */
    private $PostContract;

    /**
     * @param PostContract $PostContract
     */
    public function __construct(PostContract $PostContract)
    {
        $this->setPostContract($PostContract);
    }

    /**
     * @author Rohit Arora
     *
     * @return PostContract
     */
    public function getPostContract()
    {
        return $this->PostContract;
    }

    /**
     * @author Rohit Arora
     *
     * @param PostContract $PostContract
     */
    public function setPostContract($PostContract)
    {
        $this->PostContract = $PostContract;
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
        return $this->getPostContract()
                    ->getCommentsByPost($postID);
    }
}
