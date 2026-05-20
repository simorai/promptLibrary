<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['conversation_id', 'visibility', 'token', 'revoked_at'])]
class ConversationShare extends Model
{
    /** @use HasFactory<\Database\Factories\ConversationShareFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'revoked_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', 'public');
    }

    public function scopeRestricted(Builder $query): Builder
    {
        return $query->where('visibility', 'restricted');
    }

    public function isValid(): bool
    {
        return $this->revoked_at === null;
    }

    public function revoke(): void
    {
        $this->update(['revoked_at' => now()]);
    }

    public static function generateToken(): string
    {
        return Str::uuid()->toString();
    }
}
