<?php
/**
 * @author Rohit Arora
 */

namespace App\Exceptions;

use Exception;

/**
 * @author Rohit Arora
 */
class InvalidData extends Exception
{
    /**
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     *
     * @throws Exception
     */
    public function __construct($message = 'Invalid data requested!', $code = 400, Exception $previous = null)
    {
        \Log::debug($message);
        throw new Exception($message, $code, $previous);
    }
}