<?php

namespace App\Services;

use App\Models\Message;
use App\Models\MessageAttachment;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Smalot\PdfParser\Parser;
use Throwable;

class FileUploadService
{
    /**
     * @var array<string, list<string>>
     */
    private const ALLOWED = [
        'txt' => ['text/plain'],
        'md' => ['text/plain', 'text/markdown'],
        'pdf' => ['application/pdf'],
        'js' => ['application/javascript', 'text/javascript', 'text/plain'],
        'ts' => ['video/mp2t', 'text/plain', 'application/typescript'],
        'php' => ['application/x-httpd-php', 'text/x-php', 'text/plain'],
        'py' => ['text/x-python', 'text/plain'],
        'html' => ['text/html', 'text/plain'],
        'css' => ['text/css', 'text/plain'],
        'json' => ['application/json', 'text/plain'],
        'xml' => ['application/xml', 'text/xml', 'text/plain'],
        'csv' => ['text/csv', 'text/plain'],
    ];

    private const MAX_SIZE_BYTES = 5_242_880;

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validateFile(UploadedFile $file): array
    {
        $errors = [];
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = (string) $file->getMimeType();

        if (! array_key_exists($extension, self::ALLOWED)) {
            $errors[] = 'Unsupported file type.';
        }

        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            $errors[] = 'File exceeds the 5 MB limit.';
        }

        if (array_key_exists($extension, self::ALLOWED) && ! in_array($mime, self::ALLOWED[$extension], true)) {
            if ($mime !== 'application/octet-stream') {
                $errors[] = 'Invalid file MIME type.';
            }
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }


    // Todo Create a separate service for text extraction if we want to support more file types in the future or add more complex logic
    public function extractText(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'pdf') {
            try {
                $parser = new Parser();
                $document = $parser->parseFile($file->getRealPath());

                return trim($document->getText());
            } catch (Throwable) {
                return '';
            }
        }

        $content = file_get_contents($file->getRealPath());

        return $content !== false ? trim($content) : '';
    }

    public function processUpload(UploadedFile $file, Message $message): MessageAttachment
    {
        $validation = $this->validateFile($file);

        if (! $validation['valid']) {
            throw new RuntimeException($validation['errors'][0] ?? 'Invalid file upload.');
        }

        return $message->attachments()->create([
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => (string) $file->getMimeType(),
            'size_bytes' => $file->getSize() ?? 0,
            'extracted_text' => $this->extractText($file),
        ]);
    }
}
