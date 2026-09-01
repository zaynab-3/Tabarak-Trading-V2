<?php

namespace App\Policies;

use App\Policies\Concerns\AdminAuthorizes;

class ProductPolicy
{
    use AdminAuthorizes;
}
