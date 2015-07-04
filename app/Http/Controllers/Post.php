<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Contracts\Repository;
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
     * @var Repository
     */
    private $Repository;

    /**
     * @param Repository $Repository
     */
    public function __construct(Repository $Repository)
    {
        $this->setRepository($Repository);
    }

    /**
     * @author Rohit Arora
     *
     * @return Repository
     */
    public function getRepository()
    {
        return $this->Repository;
    }

    /**
     * @author Rohit Arora
     *
     * @param Repository $Repository
     */
    public function setRepository($Repository)
    {
        $this->Repository = $Repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return $this->getRepository()
                    ->get(\Input::only('fields'));
    }
}
