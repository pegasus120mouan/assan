<?php

namespace Tests\Unit;

use App\Support\Phone;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    public function test_it_normalizes_ivoirian_phone_numbers(): void
    {
        $this->assertSame('0701020304', Phone::normalize('07 01 02 03 04'));
        $this->assertSame('2250701020304', Phone::normalize('+225 07 01 02 03 04'));
        $this->assertNull(Phone::normalize(''));
        $this->assertNull(Phone::normalize(null));
    }
}
