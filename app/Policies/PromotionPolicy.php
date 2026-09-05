<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesStaff;

class PromotionPolicy
{
    use AuthorizesStaff;
}
