<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['user_id', 'title', 'model_used', 'is_pinned', 'pinned_at'])]
class Conversation extends Model
{
    /** @use HasFactory<\Database\Factories\ConversationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'pinned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ConversationShare::class);
    }

    public function comments(): HasManyThrough
    {
        return $this->hasManyThrough(Comment::class, Message::class, 'conversation_id', 'message_id');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    public function scopeUnpinned(Builder $query): Builder
    {
        return $query->where('is_pinned', false);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_pinned')
            ->orderByDesc('pinned_at')
            ->orderByDesc('updated_at');
    }

    public function canAccess(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function togglePin(): void
    {
        $this->is_pinned = ! $this->is_pinned;
        $this->pinned_at = $this->is_pinned ? now() : null;
        $this->save();
    }

    public function getSearchableContent(): string
    {
        $parts = array_filter([
            $this->title,
            $this->messages->pluck('content')->join("\n"),
        ]);

        return trim(implode("\n", $parts));
    }
}
