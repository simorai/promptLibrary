<?php

namespace App\Http\Requests;

use App\Models\Conversation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $conversationId = $this->integer('conversation_id');

        if (! $this->user() || $conversationId <= 0) {
            return false;
        }

        return Conversation::query()
            ->whereKey($conversationId)
            ->where('user_id', $this->user()->id)
            ->exists();
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
            'content' => ['required', 'string', 'max:10000'],
        ];
    }
}
