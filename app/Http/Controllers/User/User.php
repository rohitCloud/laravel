<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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
        parent::__construct();
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
        try {
            $users = $this->getUserContract()
                          ->fetch($this->inputs());
        } catch (\Exception $Exception) {
            return $this->responseAdapter->responseWithException($Exception);
        }

        return $this->responseAdapter->response($users);
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
        try {
            $user = $this->getUserContract()
                         ->getByID($id, $this->inputs());
        } catch (\Exception $Exception) {
            return $this->responseAdapter->responseWithException($Exception);
        }

        return $this->responseAdapter->response($user);
    }
}
