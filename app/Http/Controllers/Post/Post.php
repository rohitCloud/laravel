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
        $validator = \Validator::make($this->inputs(), [
            PostAdapter::TITLE   => 'required|string|min:3',
            PostAdapter::BODY    => 'required|string|min:10',
            PostAdapter::USER_ID => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return $this->responseAdapter->responseBadRequest($validator->errors()
                                                                        ->all());
        }

        try {
            $post = $this->getPostContract()
                         ->store($this->inputs());
        } catch (\Exception $Exception) {
            return $this->responseAdapter->responseWithException($Exception);
        }

        return $this->responseAdapter->stored($post);
    }
}
