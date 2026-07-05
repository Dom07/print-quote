<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EstimateSandboxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'length' => ['required', 'numeric', 'gt:0'],
            'width' => ['required', 'numeric', 'gt:0'],
            'gsm' => ['required', 'numeric', 'gt:0'],
            'no_of_sheets' => ['required', 'numeric', 'integer', 'min:1'],
            'no_of_sheets_with_wastage' => ['required', 'numeric', 'integer', 'min:0'],
            'no_of_sheets_to_process' => ['required', 'numeric', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'length' => 'length',
            'width' => 'width',
            'gsm' => 'GSM',
            'no_of_sheets' => 'no. of sheets',
            'no_of_sheets_with_wastage' => 'no. of sheets with wastage',
            'no_of_sheets_to_process' => 'no. of sheets to process',
        ];
    }
}
