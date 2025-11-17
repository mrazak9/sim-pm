<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RTMActionItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rtm_id',
        'action_code',
        'title',
        'description',
        'priority',
        'pic_id',
        'unit_kerja_id',
        'due_date',
        'status',
        'completion_percentage',
        'progress_notes',
        'completed_at',
        'completion_remarks',
        'evidence_file',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'date',
        'completion_percentage' => 'integer',
    ];

    /**
     * Get the RTM that owns the action item.
     */
    public function rtm(): BelongsTo
    {
        return $this->belongsTo(RTM::class, 'rtm_id');
    }

    /**
     * Get the user who is the PIC (Person In Charge) for this action item.
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    /**
     * Get the unit kerja that owns the action item.
     */
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    /**
     * Get the progress records for this action item.
     */
    public function progress(): HasMany
    {
        return $this->hasMany(RTMActionProgress::class, 'rtm_action_item_id');
    }

    /**
     * Check if action item is overdue.
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'completed' || !$this->due_date) {
            return false;
        }

        return $this->due_date < now()->toDateString();
    }

    /**
     * Check if action item is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get the number of days until the due date.
     */
    public function getDaysUntilDue(): ?int
    {
        if (!$this->due_date) {
            return null;
        }

        $diff = $this->due_date->diffInDays(now()->toDateString(), false);

        return $diff >= 0 ? $diff : null;
    }

    /**
     * Update the completion percentage based on latest progress.
     */
    public function updateCompletionPercentage(): void
    {
        $latestProgress = $this->progress()->latest('progress_date')->first();

        if ($latestProgress) {
            $this->update(['completion_percentage' => $latestProgress->progress_percentage]);
        }
    }

    /**
     * Scope a query to only include overdue action items.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
                     ->where('due_date', '<', now()->toDateString());
    }

    /**
     * Scope a query to only include completed action items.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include action items due soon.
     */
    public function scopeDueSoon($query, $days = 7)
    {
        $dueDate = now()->addDays($days)->toDateString();

        return $query->where('status', '!=', 'completed')
                     ->where('due_date', '<=', $dueDate)
                     ->where('due_date', '>=', now()->toDateString());
    }
}
