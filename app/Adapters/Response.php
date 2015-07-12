<?php
/**
 * @author Rohit Arora
 */

namespace App\Adapters;

use Illuminate\Contracts\Routing\ResponseFactory;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * @author  Rohit Arora
 *
 * Class Response
 * @package App\Adapters
 */
class Response
{
    const DATA = 'data';

    protected $statusCode = 200;

    /**
     * @author Rohit Arora
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @author Rohit Arora
     *
     * @param $statusCode
     *
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @author Rohit Arora
     *
     * @param array $data
     * @param array $headers
     *
     * @return ResponseFactory|HttpResponse
     */
    public function response($data, $headers = [])
    {
        return response($data, $this->getStatusCode(), $headers);
    }

    /**
     * @author Rohit Arora
     *
     * @param string $message
     *
     * @return ResponseFactory|HttpResponse
     */
    public function responseNotFound($message = "Not Found!")
    {
        return $this->setStatusCode(404)
                    ->response($message);
    }

    /**
     * @author Rohit Arora
     *
     * @param string $message
     *
     * @return ResponseFactory|HttpResponse
     */
    public function responseInternalError($message = 'Internal Error! We are Sorry!')
    {
        return $this->setStatusCode(500)
                    ->response($message);
    }

    /**
     * @author Rohit Arora
     *
     * @param string $message
     *
     * @return ResponseFactory|HttpResponse
     */
    public function responseWithError($message = 'Some error occurred! Sorry!')
    {
        return $this->response(['status' => $this->getStatusCode(), 'error' => ['message' => $message]]);
    }
}