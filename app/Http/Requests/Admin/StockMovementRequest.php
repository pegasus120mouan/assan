<?php

namespace App\Http\Requests\Admin;

use App\Enums\StockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockMovementRequest extends FormRequest
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
            'type' => ['required', Rule::enum(StockMovementType::class)],
            'quantity' => ['required', 'integer', 'min:0'],
            'product_variant_id' => ['nullable', 'integer'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $type = $this->input('type');
            $quantity = (int) $this->input('quantity');

            if ($type !== 'adjustment' && $quantity < 1) {
                $validator->errors()->add('quantity', 'La quantité doit être supérieure à 0.');
            }
        });
    }
}
