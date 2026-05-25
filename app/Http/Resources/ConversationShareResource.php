<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ConversationShare */
class ConversationShareResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'token' => $this->token,
            'visibility' => $this->visibility,
            'url' => route('share.show', ['token' => $this->token]),
            'created_at' => $this->created_at,
            'revoked_at' => $this->revoked_at,
        ];
    }
}
