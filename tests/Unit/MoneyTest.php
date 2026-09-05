<?php

namespace Tests\Unit;

use App\Support\Money;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    public function test_it_formats_amounts_in_fcfa_without_decimals(): void
    {
        $this->assertSame('12 500 FCFA', Money::format(12500));
        $this->assertSame('0 FCFA', Money::format(null));
    }
}
