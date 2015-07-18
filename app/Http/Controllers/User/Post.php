<?php

namespace App\Http\Controllers\User;

use App\Adapters\Post as PostAdapter;
use App\Http\Controllers\Controller;
use App\Contracts\Repositories\Post as PostContract;
use Illuminate\Http\Response;

/**
 * @author  Rohit Arora
 *
 * Class Post
 * @package App\Http\Controllers
 */
class Post extends Controller
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
        parent::__construct();
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
     * @param $userID
     *
     * @return Response
     */
    public function index($userID)
    {
        try {
            $posts = $this->getPostContract()
                          ->getPostsByUser($this->inputs(), $userID);
        } catch (\Exception $Exception) {
            return $this->responseAdapter->responseWithException($Exception);
        }

        return $this->responseAdapter->response($posts);
    }

    /**
     * @author Rohit Arora
     *
     * @param $userID
     * @param $id
     *
     * @return Response
     */
    public function show($userID, $id)
    {
        try {
            $post = $this->getPostContract()
                         ->getByUserAndID($userID, $id, $this->inputs());
        } catch (\Exception $Exception) {
            return $this->responseAdapter->responseWithException($Exception);
        }

        return $this->responseAdapter->response($post);
    }

    /**
     * @author Rohit Arora
     *
     * @param int $userID
     *
     * @return Response
     */
    public function store($userID)
    {
        $inputs                       = $this->inputs();
        $inputs[PostAdapter::USER_ID] = $userID;

        try {
            $post = $this->getPostContract()
                         ->store($inputs);
        } catch (\Exception $Exception) {
            return $this->responseAdapter->responseWithException($Exception);
        }

        return $this->responseAdapter->stored($post);
    }
}
