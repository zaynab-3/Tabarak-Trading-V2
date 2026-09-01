<?php

namespace App\Policies;

use App\Policies\Concerns\AdminAuthorizes;

class MediaPolicy
{
    use AdminAuthorizes;
}
