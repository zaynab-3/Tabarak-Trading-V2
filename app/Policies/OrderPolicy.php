<?php

namespace App\Policies;

use App\Policies\Concerns\AdminAuthorizes;

class OrderPolicy
{
    use AdminAuthorizes;
}
