<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageWithFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'conversation_id' => ['required', 'integer', 'exists:conversations,id'],
            'role' => ['required', 'in:user,assistant'],
            'content' => ['nullable', 'string', 'max:10000', 'required_without:file'],
            'file' => [
                'nullable',
                'required_without:content',
                'file',
                'max:5120',
                'mimes:txt,md,pdf,js,ts,php,py,html,css,json,xml,csv',
            ],
        ];
    }
}
