<?php

namespace Ridho\JustSubs\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Void = 'void';
    case Expired = 'expired';
}
