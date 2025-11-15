<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpmiStandard extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'objective',
        'scope',
        'reference',
        'status',
        'effective_date',
        'review_date',
        'version',
        'unit_kerja_id',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'effective_date' => 'date',
        'review_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the unit kerja that owns the SPMI standard.
     */
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    /**
     * Get the user who created the SPMI standard.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the SPMI standard.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the indicators for this SPMI standard.
     */
    public function indicators(): HasMany
    {
        return $this->hasMany(SpmiIndicator::class, 'spmi_standard_id');
    }

    /**
     * Get the monitorings for this SPMI standard.
     */
    public function monitorings(): HasMany
    {
        return $this->hasMany(SpmiMonitoring::class, 'spmi_standard_id');
    }

    /**
     * Check if standard is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if standard is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if standard is approved.
     */
    public function isApproved(): bool
    {
        return !is_null($this->approved_at) && !is_null($this->approved_by);
    }

    /**
     * Scope a query to only include active standards.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include standards by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
