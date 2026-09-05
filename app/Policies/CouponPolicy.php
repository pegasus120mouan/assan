<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesStaff;

class CouponPolicy
{
    use AuthorizesStaff;
}
