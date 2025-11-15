<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ButirComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pengisian_butir_id',
        'user_id',
        'parent_id',
        'comment',
        'is_resolved',
        'mentioned_users',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'mentioned_users' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $with = ['user'];

    /**
     * Get the pengisian butir that owns the comment.
     */
    public function pengisianButir(): BelongsTo
    {
        return $this->belongsTo(PengisianButir::class);
    }

    /**
     * Get the user who created the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (for replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ButirComment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ButirComment::class, 'parent_id')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Scope to get only root comments (not replies).
     */
    public function scopeRootComments($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get unresolved comments.
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope to get resolved comments.
     */
    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }
}
