#install

Clone source to "app/code/Coinpayments/CoinPayments" directory.

To enable module run: "bin/magento module:enable Coinpayments_CoinPayments"

In admin backend on STORES > Configuration > SALES > Payment Methods page enter clientId to send invoices and enable webHooks and enter clientSecret to receive webHook notification. 

On your first saving of configurations you create validating invoice with $ 0.01 cost, or creating webHook notification, if enabled.

On /checkout/#payment page after placing orders you'll gonna be redirected to coin checkout page.