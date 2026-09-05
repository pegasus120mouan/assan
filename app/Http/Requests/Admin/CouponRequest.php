<?php

namespace App\Http\Requests\Admin;

use App\Enums\CouponType;
use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $coupon = $this->route('coupon');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($coupon)],
            'type' => ['required', Rule::enum(CouponType::class)],
            'value' => ['required', 'integer', 'min:1'],
            'minimum_order_amount' => ['nullable', 'integer', 'min:0'],
            'maximum_discount' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_per_customer' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', Rule::enum(Status::class)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function couponData(): array
    {
        $data = $this->validated();
        $data['code'] = mb_strtoupper((string) $data['code']);
        $data['minimum_order_amount'] = (int) ($data['minimum_order_amount'] ?? 0);

        return $data;
    }
}
