<?php
/**
 * @author Rohit Arora
 */

namespace App\Exceptions;

use Exception;

/**
 * @author Rohit Arora
 */
class InvalidArguments extends Exception
{
    /**
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     *
     * @throws Exception
     */
    public function __construct($message = 'Invalid arguments supplied!', $code = 400, Exception $previous = null)
    {
        throw new Exception($message, $code, $previous);
    }
}