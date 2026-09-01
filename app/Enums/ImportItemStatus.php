<?php

namespace App\Enums;

enum ImportItemStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Review = 'review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Failed = 'failed';
}
