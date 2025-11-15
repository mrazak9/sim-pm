<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_id',
        'uploaded_by',
        'version_number',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'change_notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'version_number' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * Get the document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the uploader
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
