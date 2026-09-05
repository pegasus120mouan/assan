<?php

namespace App\Http\Requests\Storefront;

use App\Enums\DeliveryMethod;
use App\Services\Payments\PaymentService;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_phone' => Phone::normalize($this->input('customer_phone')) ?? $this->input('customer_phone'),
            'delivery_city' => $this->input('delivery_city') ?: 'Abidjan',
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'delivery_address' => ['required', 'string', 'max:255'],
            'delivery_commune_id' => ['nullable', 'integer', 'exists:communes,id'],
            'delivery_commune' => ['nullable', 'string', 'max:120'],
            'delivery_city' => ['required', 'string', 'max:120'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
            'delivery_method' => ['required', Rule::enum(DeliveryMethod::class)],
            'payment_method' => ['required', Rule::in(app(PaymentService::class)->enabledKeys())],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_name' => 'nom',
            'customer_phone' => 'téléphone',
            'customer_email' => 'e-mail',
            'delivery_address' => 'adresse',
            'delivery_commune_id' => 'commune',
            'delivery_commune' => 'commune',
            'delivery_city' => 'ville',
            'customer_notes' => 'notes',
            'delivery_method' => 'mode de livraison',
            'payment_method' => 'mode de paiement',
        ];
    }
}
