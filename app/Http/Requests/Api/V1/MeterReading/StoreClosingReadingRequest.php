<?php

namespace App\Http\Requests\Api\V1\MeterReading;

use Illuminate\Foundation\Http\FormRequest;

class StoreClosingReadingRequest extends FormRequest
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
            'shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
            'nozzle_id' => ['required', 'integer', 'exists:nozzles,id'],
            'closing_reading' => ['required', 'numeric', 'min:0'],
        ];
    }
}
