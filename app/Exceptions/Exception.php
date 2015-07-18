<?php
/**
 * @author Rohit Arora
 */

namespace App\Exceptions;

/**
 * @author  Rohit Arora
 *
 * Class Exception
 * @package App\Exceptions
 */
class Exception extends \Exception
{
    /**
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     *
     * @throws \Exception
     */
    public function __construct($message = 'Exception!', $code = 400, Exception $previous = null)
    {
        if (is_array($message)) {
            $message = implode(', ', $message);
        }

        throw new \Exception($message, $code, $previous);
    }
}