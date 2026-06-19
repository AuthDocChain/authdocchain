<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CertifyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'document'        => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:20480'],
            'name'            => ['nullable', 'string', 'max:255'],
            'type'            => ['required', 'string', 'in:diploma,certificate,contract,invoice,report,identity,other'],
            'physical'        => ['nullable', 'boolean'],
            'qr_position'     => ['nullable', 'string', 'in:top-left,top-center,top-right,bottom-left,bottom-center,bottom-right'],
            'stamp_all_pages' => ['nullable', 'boolean'],
            'qr_size'         => ['nullable', 'integer', 'min:30', 'max:80'],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'Please select a file to certify.',
            'document.mimes'    => 'Only PDF, PNG and JPG files are accepted (max 20 MB).',
            'document.max'      => 'File size must not exceed 20 MB.',
            'type.required'     => 'Please select a document type.',
        ];
    }
}
