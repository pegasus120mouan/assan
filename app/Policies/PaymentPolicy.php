<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesStaff;

class PaymentPolicy
{
    use AuthorizesStaff;
}
