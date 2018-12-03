<?php

namespace UfoEngineering\Wallet;

use UfoEngineering\Wallet\Exception\FailedWalletTransactionException;
use Exception;
use DB;

trait HasWallet
{
    /**
     * Retrieve the balance of this user's wallet
     */
    public function getBalanceAttribute()
    {
        return $this->wallet->balance;
    }

    /**
     * Retrieve the wallet of this user
     */
    public function wallet()
    {
        return $this->hasOne(config('wallet.wallet_model', Wallet::class))->withDefault();
    }

    /**
     * Retrieve all transactions of this user
     */
    public function transactions()
    {
        return $this->hasManyThrough(config('wallet.transaction_model', Transaction::class), config('wallet.wallet_model', Wallet::class))->latest();
    }

    /**
     * Determine if the user can withdraw the given amount
     * @param  integer $amount
     * @return boolean
     */
    public function canWithdraw($amount)
    {
        return $this->balance >= $amount;
    }

    /**
     * Move credits to this account
     * @param  integer $amount
     * @param  string  $type
     * @param  array   $meta
     *
     * @throws UfoEngineering\Wallet\Exception\FailedWalletTransactionException
     * @return boolean
     */
    public function deposit($amount, $type = 'deposit', $meta = [], $accepted = true) : bool
    {
        $transaction_status = false;
        try {
            DB::beginTransaction();
                if ($accepted) {
                    $this->wallet->balance += $amount;
                    $transaction_status = $this->wallet->save();
                } elseif (! $this->wallet->exists) {
                    $transaction_status = $this->wallet->save();
                }

                if ($transaction_status) {
                    $this->wallet->transactions()
                        ->create([
                            'amount' => $amount,
                            'hash' => uniqid('lwch_'),
                            'type' => $type,
                            'accepted' => $accepted,
                            'meta' => $meta
                        ]);
                }

            DB::commit();
        } catch (Exception $e) {
            throw new FailedWalletTransactionException('Fail move credits to this account', null, $e);
        }

        return $transaction_status;
    }

    /**
     * Fail to move credits to this account
     * @param  integer $amount
     * @param  string  $type
     * @param  array   $meta
     */
    public function failDeposit($amount, $type = 'deposit', $meta = [])
    {
        $this->deposit($amount, $type, $meta, false);
    }

    /**
     * Attempt to move credits from this account
     * @param  integer $amount
     * @param  string  $type
     * @param  array   $meta
     * @param  boolean $shouldAccept
     *
     * @throws UfoEngineering\Wallet\Exception\FailedWalletTransactionException
     * @return boolean
     */
    public function withdraw($amount, $type = 'withdraw', $meta = [], $shouldAccept = true) : bool
    {
        $transaction_status = false;
        try {
            DB::beginTransaction();
                $accepted = $shouldAccept ? $this->canWithdraw($amount) : true;

                if ($accepted) {
                    $this->wallet->balance -= $amount;
                    $transaction_status = $this->wallet->save();
                } elseif (! $this->wallet->exists) {
                    $transaction_status = $this->wallet->save();
                }

                if ($transaction_status) {
                    $this->wallet->transactions()
                        ->create([
                            'amount' => $amount,
                            'hash' => uniqid('lwch_'),
                            'type' => $type,
                            'accepted' => $accepted,
                            'meta' => $meta
                        ]);
                }

            DB::commit();
        } catch (Exception $e) {
            throw new FailedWalletTransactionException('Fail move credits from this account', null, $e);
        }

        return $transaction_status;
    }

    /**
     * Move credits from this account
     * @param  integer $amount
     * @param  string  $type
     * @param  array   $meta
     * @param  boolean $shouldAccept
     */
    public function forceWithdraw($amount, $type = 'withdraw', $meta = [])
    {
        return $this->withdraw($amount, $type, $meta, false);
    }

    /**
     * Returns the actual balance for this wallet.
     * Might be different from the balance property if the database is manipulated
     * @return float balance
     */
    public function actualBalance()
    {
        $credits = $this->wallet->transactions()
            ->whereIn('type', ['deposit', 'refund'])
            ->where('accepted', 1)
            ->sum('amount');

        $debits = $this->wallet->transactions()
            ->whereIn('type', ['withdraw', 'payout'])
            ->where('accepted', 1)
            ->sum('amount');

        return $credits - $debits;
    }
}
