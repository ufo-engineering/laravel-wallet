<?php

namespace UfoEngineering\Wallet\Exception;

use Exception;

class WalletNotExistsException extends Exception
{
    protected $message;

    /**
     * Constructor.
     *
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        $this->message = $message;
    }
}
