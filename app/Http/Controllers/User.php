<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Contracts\Repositories\User as UserContract;
use Illuminate\Http\Response;

/**
 * @author  Rohit Arora
 *
 * Class User
 * @package App\Http\Controllers
 */
class User extends Controller
{
    /**
     * @var UserContract
     */
    private $UserContract;

    /**
     * @param UserContract $UserContract
     */
    public function __construct(UserContract $UserContract)
    {
        $this->setUserContract($UserContract);
    }

    /**
     * @author Rohit Arora
     *
     * @return UserContract
     */
    public function getUserContract()
    {
        return $this->UserContract;
    }

    /**
     * @author Rohit Arora
     *
     * @param UserContract $UserContract
     */
    public function setUserContract($UserContract)
    {
        $this->UserContract = $UserContract;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return $this->getUserContract()
                    ->get(\Input::only('fields'));
    }
}
