<?php
/**
 * @author Rohit Arora
 */

namespace App\Adapters;

use Exception;
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
        $data = ['status' => $this->getStatusCode()] + (array) $data;

        return $this->respond($data, $headers);
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
                    ->respondWithError($message);
    }

    /**
     * @author Rohit Arora
     *
     * @param string $message
     *
     * @return ResponseFactory|HttpResponse
     */
    public function responseBadRequest($message = "Bad Request!")
    {
        return $this->setStatusCode(400)
                    ->respondWithError($message);
    }

    /**
     * @author Rohit Arora
     *
     * @param Exception $e
     *
     * @return ResponseFactory|HttpResponse
     */
    public function responseWithException(Exception $e)
    {
        \Log::error($e);
        if ($e->getCode() > 0) {
            return $this->setStatusCode($e->getCode())
                        ->respondWithError($e->getMessage());
        } else {
            return $this->responseInternalError();
        }
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
                    ->respondWithError($message);
    }

    /**
     * @author Rohit Arora
     *
     * @param string|array $message
     *
     * @return ResponseFactory|HttpResponse
     */
    public function respondWithError($message = 'Some error occurred! Sorry!')
    {
        return $this->respond(['status' => $this->getStatusCode(), 'error' => ['message' => $message]]);
    }

    /**
     * @author Rohit Arora
     *
     * @param array $data
     * @param array $headers
     *
     * @return ResponseFactory|HttpResponse
     */
    private function respond($data, $headers = [])
    {
        return response($data, $this->getStatusCode(), $headers);
    }

    /**
     * @author Rohit Arora
     *
     * @param array $data
     * @param array $headers
     *
     * @return ResponseFactory|HttpResponse
     */
    public function stored($data, $headers = [])
    {
        return $this->setStatusCode(201)
                    ->response($data, $headers);
    }
}