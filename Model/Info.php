<?php

namespace Coinpayments\CoinPayments\Model;

class Info
{
    const PAYMENT_STATUS_REFUND = 'refund';
    const PAYMENT_STATUS_PENDING = 'pending';

    const PAYMENT_STATUS_CANCELLED = 'cancelled';

    const PAYMENT_STATUS_WAITING_FOR_FUNDS = 'processing';

    const PAYMENT_STATUS_COIN_CONFIRMED = 'confirmed';

    const PAYMENT_STATUS_QUEUE = 'queue';

    const PAYMENT_STATUS_HOLD = 'hold';

    const PAYMENT_STATUS_COMPLETE = 'complete';

    const IPN_TYPE_SIMPLE = 'simple';

    const IPN_TYPE_BUTTON = 'button';

    const IPN_TYPE_CART = 'cart';

    const IPN_TYPE_DONATION = 'donation';

    const IPN_TYPE_DEPOSIT = 'deposit';

    const IPN_TYPE_API = 'api';
}