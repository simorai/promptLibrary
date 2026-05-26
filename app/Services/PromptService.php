<?php

namespace App\Services;

use App\Models\Prompt;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PromptService
{
    public function create(User $user, string $name, string $text): Prompt
    {
        return $user->prompts()->create([
            'name' => trim($name),
            'text' => trim($text),
        ]);
    }

    /**
     * @param array{name?: string, text?: string} $data
     */
    public function update(Prompt $prompt, array $data): Prompt
    {
        $payload = [];

        if (array_key_exists('name', $data)) {
            $payload['name'] = trim((string) $data['name']);
        }

        if (array_key_exists('text', $data)) {
            $payload['text'] = trim((string) $data['text']);
        }

        $prompt->update($payload);

        return $prompt->refresh();
    }

    public function delete(Prompt $prompt): bool
    {
        return (bool) $prompt->delete();
    }

    public function search(User $user, ?string $query): Collection
    {
        return Prompt::query()
            ->forUser($user->id)
            ->search($query)
            ->latest()
            ->get();
    }

    public function validateName(string $name): bool
    {
        return mb_strlen(trim($name)) > 0 && mb_strlen(trim($name)) <= 80;
    }

    public function validateText(string $text): bool
    {
        return mb_strlen(trim($text)) > 0 && mb_strlen(trim($text)) <= 2000;
    }
}
