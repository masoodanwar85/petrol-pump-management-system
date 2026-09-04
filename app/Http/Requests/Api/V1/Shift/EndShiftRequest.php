<?php

namespace App\Http\Requests\Api\V1\Shift;

use Illuminate\Foundation\Http\FormRequest;

class EndShiftRequest extends FormRequest
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
            'end_time' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'readings' => ['nullable', 'array', 'min:1'],
            'readings.*.nozzle_id' => ['required_with:readings', 'integer', 'distinct', 'exists:nozzles,id'],
            'readings.*.closing_reading' => ['required_with:readings', 'numeric', 'min:0'],
        ];
    }
}
