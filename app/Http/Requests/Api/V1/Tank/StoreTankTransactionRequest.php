<?php

namespace App\Http\Requests\Api\V1\Tank;

use App\Enums\TankTransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTankTransactionRequest extends FormRequest
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
            'type' => ['required', Rule::enum(TankTransactionType::class)],
            'quantity_liters' => ['required', 'numeric'],
            'cost_per_liter' => ['required_if:type,purchase', 'nullable', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
