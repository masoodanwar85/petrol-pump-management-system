<?php

namespace App\Http\Requests\Api\V1\Shift;

use Illuminate\Foundation\Http\FormRequest;

class StartShiftRequest extends FormRequest
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
            'start_time' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'readings' => ['required', 'array', 'min:1'],
            'readings.*.nozzle_id' => ['required', 'integer', 'distinct', 'exists:nozzles,id'],
            'readings.*.opening_reading' => ['required', 'numeric', 'min:0'],
        ];
    }
}
