<?php
/**
 * @author Rohit Arora
 */

namespace App\Exceptions;

/**
 * @author Rohit Arora
 */
class NotFound extends Exception
{
    /**
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     *
     * @throws Exception
     */
    public function __construct($message = 'Data not available!', $code = 404, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
