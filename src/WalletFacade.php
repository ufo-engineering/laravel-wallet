<?php

namespace UfoEngineering\Wallet;

use Illuminate\Support\Facades\Facade;

/**
 * @see \UfoEngineering\Wallet\Wallet
 */
class WalletFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'wallet';
    }
}
