<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StreamConversationMessageRequest extends FormRequest
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
            'model' => ['required', 'string', 'max:190'],
            'content' => ['nullable', 'string', 'max:10000', 'required_without:file'],
            'temperature' => ['nullable', 'numeric', 'between:0,2'],
            'max_tokens' => ['nullable', 'integer', 'between:1,8192'],
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
