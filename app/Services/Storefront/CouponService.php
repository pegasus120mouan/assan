<?php

namespace App\Services\Storefront;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public const SESSION_KEY = 'coupon_code';

    public function current(): ?Coupon
    {
        $code = session(self::SESSION_KEY);

        if (! is_string($code) || $code === '') {
            return null;
        }

        return Coupon::query()->where('code', $code)->first();
    }

    public function apply(string $code, int $subtotal, ?User $user = null): Coupon
    {
        $coupon = $this->findUsable($code, $subtotal, $user);
        session([self::SESSION_KEY => $coupon->code]);

        return $coupon;
    }

    public function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function discountFor(int $subtotal, ?User $user = null): int
    {
        $coupon = $this->current();

        if (! $coupon) {
            return 0;
        }

        try {
            $this->assertUsable($coupon, $subtotal, $user);
        } catch (ValidationException) {
            $this->forget();

            return 0;
        }

        return $this->amount($coupon, $subtotal);
    }

    public function redeem(Order $order, int $subtotal): int
    {
        $coupon = $this->current();

        if (! $coupon) {
            return 0;
        }

        $this->assertUsable($coupon, $subtotal, $order->user);
        $discount = $this->amount($coupon, $subtotal);

        $coupon->usages()->create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'discount_amount' => $discount,
        ]);

        $coupon->increment('used_count');
        $this->forget();

        return $discount;
    }

    public function amount(Coupon $coupon, int $subtotal): int
    {
        $raw = match ($coupon->type) {
            CouponType::Fixed => (int) $coupon->value,
            CouponType::Percentage => (int) round($subtotal * $coupon->value / 100),
        };

        if ($coupon->maximum_discount !== null) {
            $raw = min($raw, (int) $coupon->maximum_discount);
        }

        return max(0, min($raw, $subtotal));
    }

    private function findUsable(string $code, int $subtotal, ?User $user): Coupon
    {
        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [mb_strtoupper(trim($code))])
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'code' => 'Ce code promo n’existe pas.',
            ]);
        }

        $this->assertUsable($coupon, $subtotal, $user);

        return $coupon;
    }

    private function assertUsable(Coupon $coupon, int $subtotal, ?User $user): void
    {
        if (! $coupon->isUsable()) {
            throw ValidationException::withMessages([
                'code' => 'Ce code promo n’est plus valable.',
            ]);
        }

        if ($subtotal < (int) $coupon->minimum_order_amount) {
            throw ValidationException::withMessages([
                'code' => 'Montant minimum : '.format_price((int) $coupon->minimum_order_amount).'.',
            ]);
        }

        if ($user && $coupon->usage_per_customer) {
            $used = $coupon->usages()->where('user_id', $user->id)->count();

            if ($used >= (int) $coupon->usage_per_customer) {
                throw ValidationException::withMessages([
                    'code' => 'Vous avez déjà utilisé ce code promo.',
                ]);
            }
        }
    }
}
