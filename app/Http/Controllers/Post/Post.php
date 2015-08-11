<?php

namespace App\Http\Controllers\Post;

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
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        try {
            $posts = $this->getPostContract()
                          ->fetch($this->inputs());
        } catch (\Exception $Exception) {
            return $this->responseAdapter->responseWithException($Exception);
        }

        return $this->responseAdapter->response($posts);
    }

    /**
     * @author Rohit Arora
     *
     * @param $id
     *
     * @return Response
     */
    public function show($id)
    {
        try {
            $post = $this->getPostContract()
                         ->getByID($id, $this->inputs());
        } catch (\Exception $Exception) {
            return $this->responseAdapter->responseWithException($Exception);
        }

        return $this->responseAdapter->response($post);
    }

    /**
     * @author Rohit Arora
     *
     * @return Response
     */
    public function store()
    {
        try {
            $post = $this->getPostContract()
                         ->store($this->inputs());
        } catch (\Exception $Exception) {
            return $this->responseAdapter->responseWithException($Exception);
        }

        return $this->responseAdapter->stored($post);
    }

    /**
     * @author Rohit Arora
     *
     * @param $postID
     *
     * @return string
     */
    public function update($postID)
    {
        try {
            $post = $this->PostContract->modify(\Input::only([PostAdapter::TITLE, PostAdapter::BODY]), $postID);
        } catch (\Exception $Exception) {
            return $this->responseAdapter->responseWithException($Exception);
        }

        return $this->responseAdapter->response($post);
    }

    /**
     * @author Rohit Arora
     *
     * @param $postID
     *
     * @return Response
     */
    public function destroy($postID)
    {
        try {
            $this->PostContract->destroy($postID);
        } catch (\Exception $Exception) {
            return $this->responseAdapter->responseWithException($Exception);
        }

        return $this->responseAdapter->response();
    }
}
