<?php

namespace App\Policies;

use App\Policies\Concerns\AdminAuthorizes;

class ImportBatchPolicy
{
    use AdminAuthorizes;
}
