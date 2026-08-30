<?php

namespace Ridho\JustSubs\Exceptions;

use Exception;

class PaymentFailedException extends Exception
{
    public static function alreadyPaid(): self
    {
        return new self('Invoice is already paid.');
    }

    public static function invalidAmount(): self
    {
        return new self('Payment amount does not match invoice amount.');
    }

    public static function invalidCurrency(): self
    {
        return new self('Payment currency does not match invoice currency.');
    }

    public static function invalidInvoiceStatus(): self
    {
        return new self('Invoice is not in a payable state.');
    }
}
