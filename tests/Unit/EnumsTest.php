<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    public function test_user_role_identifies_staff(): void
    {
        $this->assertTrue(UserRole::Admin->isStaff());
        $this->assertTrue(UserRole::Manager->isStaff());
        $this->assertFalse(UserRole::Customer->isStaff());
        $this->assertSame([UserRole::Admin, UserRole::Manager], UserRole::staffCases());
    }

    public function test_order_status_final_states(): void
    {
        $this->assertTrue(OrderStatus::Delivered->isFinal());
        $this->assertTrue(OrderStatus::Cancelled->isFinal());
        $this->assertFalse(OrderStatus::Pending->isFinal());
    }

    public function test_stock_movement_types_that_increase_stock(): void
    {
        $this->assertTrue(StockMovementType::Purchase->increasesStock());
        $this->assertTrue(StockMovementType::Return->increasesStock());
        $this->assertFalse(StockMovementType::Sale->increasesStock());
        $this->assertFalse(StockMovementType::Damaged->increasesStock());
        $this->assertTrue(StockMovementType::Sale->decreasesStock());
        $this->assertTrue(StockMovementType::Damaged->decreasesStock());
        $this->assertFalse(StockMovementType::Purchase->decreasesStock());
    }
}
