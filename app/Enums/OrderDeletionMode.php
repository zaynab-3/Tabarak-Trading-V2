<?php

namespace App\Enums;

enum OrderDeletionMode: string
{
    case CancelAndRestoreStock = 'cancel_restore_stock';
    case DeleteRecordOnly = 'delete_record_only';
}
