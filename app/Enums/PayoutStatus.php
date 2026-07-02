<?php

namespace App\Enums;

enum PayoutStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Paid = 'paid';
}
