<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesStaff;

class DeliveryFeePolicy
{
    use AuthorizesStaff;
}
