<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Assigned = 'assigned';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
