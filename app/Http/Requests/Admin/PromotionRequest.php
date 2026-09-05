<?php

namespace App\Http\Requests\Admin;

use App\Enums\DiscountType;
use App\Enums\PromotionType;
use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromotionRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(PromotionType::class)],
            'discount_type' => ['required', Rule::enum(DiscountType::class)],
            'value' => ['required', 'integer', 'min:0'],
            'product_id' => ['nullable', 'integer', 'exists:products,id', 'required_if:type,product'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id', 'required_if:type,category'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::enum(Status::class)],
            'priority' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function promotionData(): array
    {
        $data = $this->validated();
        $data['product_id'] = $data['product_id'] ?? null;
        $data['category_id'] = $data['category_id'] ?? null;
        $data['priority'] = (int) ($data['priority'] ?? 0);

        return $data;
    }
}
