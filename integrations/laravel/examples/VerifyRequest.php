<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $mode = $this->input('mode', 'file');

        return $mode === 'file'
            ? ['document'     => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:20480'],
               'mode'         => ['nullable', 'string']]
            : ['fingerprint'  => ['required', 'string', 'min:10', 'max:128'],
               'mode'         => ['nullable', 'string']];
    }

    public function messages(): array
    {
        return [
            'document.required'    => 'Please upload the document to verify.',
            'fingerprint.required' => 'Please enter a document reference.',
            'fingerprint.min'      => 'The reference is too short.',
        ];
    }
}
