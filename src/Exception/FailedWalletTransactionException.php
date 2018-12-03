<?php

namespace UfoEngineering\Wallet\Exception;

use Exception;
use DB;
use Log;

class FailedWalletTransactionException extends Exception
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
        DB::rollBack();
        Log::error($previous->getMessage());
        Log::error($previous->getTraceAsString());
        $this->message = $message;
    }
}
