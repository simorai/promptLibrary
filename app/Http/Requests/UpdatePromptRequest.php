<?php

namespace App\Http\Requests;

use App\Models\Prompt;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePromptRequest extends FormRequest
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
        /** @var Prompt $prompt */
        $prompt = $this->route('prompt');

        return [
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('prompts', 'name')
                    ->ignore($prompt->id)
                    ->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'text' => ['required', 'string', 'max:2000'],
        ];
    }
}
