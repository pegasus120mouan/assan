<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesStaff;

class DeliveryPolicy
{
    use AuthorizesStaff;
}
