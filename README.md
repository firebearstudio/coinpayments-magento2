<h2>Overview</h2>
<p dir="ltr"><span>Meet the best way to accept cryptocurrency on <strong>Magento 2</strong>. With <strong>Firebear CoinPayments Magento 2</strong> extension, you will easily integrate your ecommerce store with the popular cryptocurrency gateway accepting over 70 altcoins, including Bitcoin and Ethereum. Thus, Magento 2 cryptocurrency trading is no longer an unattainable goal - it is a new way of business dealing.</span></p>
<p dir="ltr"><strong>Features</strong></p>
<p>- Magento 2 cryptocurrency integration: accept Bitcoin, Ethereum, and other altcoins as payment for products and services you sell;</p>
<p>- Use Magento 2 cryptocurrency wallets (based on CoinPayments) to store crypto: Ethereum, Bitcoin or any supported altcoin wallet;</p>
<p>- Transparent transaction history: view separate logs for deposits, transfers, withdrawals, and conversions.</p>
<p>- Supported coins : Bitcoin, Litecoin, AudioCoin, BitConnect, Bitcoin Cash, BitBean, BlackCoin, Breakout, CloakCoin, Crown, CureCoin, Dash, Decred, DigiByte, Dogecoin, Ether Classic, Ethereum, Expanse, FLASH, GameCredits, GCRCoin, Goldcoin, Gridcoin, Groestlcoin, LeoCoin, LeoCoin (Old Chain), LISK, MaidSafeCoin, MonetaryUnit, NAV Coin, NoLimitCoin, Namecoin, NVO Token, Nexus, NXT, OMNI, PinkCoin, PIVX, PoSW Coin, PotCoin, Peercoin, ProCurrency, Quark, Steem Dollars, SibCoin, STEEM, Stratis, Syscoin, TetherUSD, Voxels, Vertcoin, Waves, Counterparty, NEM, Monero, VERGE, ZCash, ZenCash.</p>
<p dir="ltr"><span><a href="https://www.coinpayments.net/index.php?ref=606a89bb575311badf510a4a8b79a45e" target="_blank"><strong>CoinPayments</strong></a> is a popular online platform that allows accepting, storing, converting, and withdrawing altcoins. Currently it supports 70+ cryptocurrencies and provides a unique $tag to receive payments from all of them. Thus, you can easily accept payments in such popular altcoins as Bitcoin and Ethereum on your Magento 2 ecommerce website. Bitcoin is a number one cryptocurrency that has become the first decentralized digital currency that uses peer-to-peer transactions, so users interact directly without any intermediary. Ethereum is based on the same technology, blockchain, and provides a cryptocurrency token transferable between accounts as well. To view current Magento 2 cryptocurrency prices, visit </span><a href="https://coinmarketcap.com/"><span>CoinMarketCup</span></a><span>.</span></p>
<p dir="ltr"><span>With the Firebear CoinPayments Magento 2 cryptocurrency extension, you can not only accept altcoins on your Magento 2 website, but also store cryptocurrency in a secure online wallet as well as protect altcoins in the vault that requires a time amount before being able to spend them. Almost 400 thousand vendors all over the world already use CoinPayments, so don't waste your chance to implement the new technology on your ecommerce storefront with the Firebear CoinPayments Magento 2 extension.</span></p>
<p dir="ltr"><span>The <strong>Magento 2 CoinPyament cryptocurrency</strong> module is easy to configure. The integration of your store with CoinPayments (and the desired ability to accept crypto on Magento 2) won't take much time. Simply go to Stores -&gt; Settings -&gt; Configuration -&gt; Sales -&gt; Payment Methods -&gt; Other Payment Methods. Here, you can find the 'Coin Payments' section that allows enabling the integration (specify such parameters as your CoinPayments.net Merchant ID and IPN secret), specifying countries to enable the new payment method for, and selecting order statuses for two cases: CoinPayments didn't receive funds and funds are received.</span></p>

<h2>Installation</h2>
Composer driven installation coming soon , for now please use manual approach described below.<br /><br />

1. Create backups of your web directory and Magento 2 store database;<br />
2. Download Firebear CoinPayments Magento 2 Extension installation package;<br />
3. Unzip (extract from zip archive) file and copy to /app/code/Firebear/CoinPayments/ folder (create it manually!)<br />
4. Navigate to your store root folder in the SSH console of your server:<br /><br />

cd path_to_the_store_root_folder<br />

And run the following commands:<br />

php -f bin/magento module:enable Firebear_CoinPayments<br />
php -f bin/magento setup:upgrade<br />

5. Now, you have to flush store cache; log out from your backend and login once again. Use the following command:<br />

php -f bin/magento cache:clean<br /><br />

Congratulations! The Firebear CoinPayments Magento 2 Bitcoin/Ethereum/Altcoin module is successfully installed. Now, you should configure your new Magento 2 cryptocurrency payment gateway.

<h2>User Experience</h2>
<p dir="ltr"><span>From the frontend perspective, you should add a product to cart and proceed to checkout. Complete the first step in order to be able to select a payment method. Select "Coin Payments" and you will see the following message:</span></p>
<p><img src="https://firebearstudio.com/media/wysiwyg/Magento_2_CoinPayments_Configuration_checkout.gif" alt="magento 2 bitcoin checkout" width="515" /></p>
<p dir="ltr"><span>Now, you are on the CoinPayments platform. Choose your altcoin, and complete the checkout. Alternatively, you can cancel it and return to seller's store, contact seller directly from the cryptocurrency payment gateway page, or view profile.</span></p>
<p><img src="https://firebearstudio.com/media/wysiwyg/Checkout_last_step.jpg" alt="send bitcoin magento 2" width="515" /></p>
