<?php

namespace App\Enums;

enum RepaymentStatus: string
{
    case PAID         = 'paid';
    case UNPAID       = 'unpaid';
    case PARTIAL_PAID = 'partial_paid';
}
