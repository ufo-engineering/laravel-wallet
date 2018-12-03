# Changelog

All notable changes to `laravel-wallet` will be documented in this file

## 1.0.0 - 2018-02-20

- initial release

### Changes:
* modify migration. PrimaryKey(int) -> (uuid)
* Added db transaction
* added UfoEngineering\Wallet\Exception\FailedWalletTransactionException
* UfoEngineering\Wallet\HasWallet withdraw() and deposit() return boolean
