<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PengisianButirLock extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'pengisian_butir_id',
        'user_id',
        'locked_at',
        'expires_at',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $with = ['user'];

    /**
     * Get the pengisian butir that is locked.
     */
    public function pengisianButir(): BelongsTo
    {
        return $this->belongsTo(PengisianButir::class);
    }

    /**
     * Get the user who locked the pengisian butir.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the lock is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the lock is still active.
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Get remaining time in minutes.
     */
    public function getRemainingMinutes(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return (int) Carbon::now()->diffInMinutes($this->expires_at, false);
    }

    /**
     * Scope to get active locks only.
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', Carbon::now());
    }

    /**
     * Scope to get expired locks.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', Carbon::now());
    }
}
