<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['message_id', 'original_filename', 'mime_type', 'size_bytes', 'extracted_text'])]
class MessageAttachment extends Model
{
    /** @use HasFactory<\Database\Factories\MessageAttachmentFactory> */
    use HasFactory;

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
