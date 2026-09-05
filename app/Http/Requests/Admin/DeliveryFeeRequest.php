<?php

namespace App\Http\Requests\Admin;

use App\Enums\DeliveryMethod;
use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryFeeRequest extends FormRequest
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
        return [
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'commune_id' => ['nullable', 'integer', 'exists:communes,id'],
            'zone_id' => ['nullable', 'integer', 'exists:zones,id'],
            'delivery_method' => ['required', Rule::enum(DeliveryMethod::class)],
            'min_order_amount' => ['nullable', 'integer', 'min:0'],
            'max_order_amount' => ['nullable', 'integer', 'min:0'],
            'min_weight' => ['nullable', 'numeric', 'min:0'],
            'max_weight' => ['nullable', 'numeric', 'min:0'],
            'fee' => ['required', 'integer', 'min:0'],
            'free_above_amount' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(Status::class)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function feeData(): array
    {
        $data = $this->validated();
        $data['min_order_amount'] = (int) ($data['min_order_amount'] ?? 0);

        return $data;
    }
}
