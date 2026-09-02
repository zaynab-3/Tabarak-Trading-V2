<?php

namespace App\Policies;

use App\Policies\Concerns\AdminAuthorizes;

class OrderDeletionNoticePolicy
{
    use AdminAuthorizes;
}
