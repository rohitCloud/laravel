<?php

namespace App\Http\Controllers;

use App\Adapters\Response;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @author  Rohit Arora
 *
 * Class Controller
 * @package App\Http\Controllers
 */
abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    /** @var Response */
    protected $response;

    /**
     * @author Rohit Arora
     */
    public function __construct()
    {
        $this->response = new Response();
    }


    /**
     * @author Rohit Arora
     *
     * @return array
     */
    protected function inputFilter()
    {
        return \Input::all();
    }
}
