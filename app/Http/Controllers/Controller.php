<?php

namespace App\Http\Controllers;

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

    /**
     * @author Rohit Arora
     *
     * @return array
     */
    protected function inputFilter()
    {
        return \Input::only('fields', 'limit', 'offset', 'sort_by', 'sort_type');
    }
}
