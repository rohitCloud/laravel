<?php

namespace App\Http\Controllers;

use App\Http\Requests;
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
        return $this->getPostContract()
                    ->get(\Input::only('fields', 'limit', 'offset'));
    }
}
