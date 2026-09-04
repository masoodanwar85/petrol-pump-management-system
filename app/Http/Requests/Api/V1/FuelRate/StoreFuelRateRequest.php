<?php

namespace App\Http\Requests\Api\V1\FuelRate;

use Illuminate\Foundation\Http\FormRequest;

class StoreFuelRateRequest extends FormRequest
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
            'rate' => ['required', 'numeric', 'min:0.01'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after:effective_from'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
