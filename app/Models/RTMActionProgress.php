<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RTMActionProgress extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rtm_action_item_id',
        'progress_date',
        'progress_percentage',
        'description',
        'evidence_file',
        'reported_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'progress_date' => 'date',
        'progress_percentage' => 'integer',
    ];

    /**
     * Get the RTM action item that owns the progress.
     */
    public function rtmActionItem(): BelongsTo
    {
        return $this->belongsTo(RTMActionItem::class, 'rtm_action_item_id');
    }

    /**
     * Get the user who reported the progress.
     */
    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($progress) {
            // Auto-update parent RTMActionItem's completion_percentage to latest progress
            $actionItem = $progress->rtmActionItem;
            if ($actionItem) {
                $actionItem->update(['completion_percentage' => $progress->progress_percentage]);
            }
        });
    }
}
