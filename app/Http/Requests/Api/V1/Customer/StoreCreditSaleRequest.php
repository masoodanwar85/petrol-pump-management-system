<?php

namespace App\Http\Requests\Api\V1\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fuel_type_id' => ['required', 'integer', 'exists:fuel_types,id'],
            'liters' => ['required', 'numeric', 'min:0.001'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
            'date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
