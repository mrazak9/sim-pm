<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DocumentShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'shared_by',
        'shared_with',
        'permission',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user who shared
     */
    public function sharer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    /**
     * Get the user shared with
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_with');
    }

    /**
     * Check if share is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if share is active
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Scope: active shares
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    /**
     * Scope: expired shares
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', Carbon::now());
    }
}
