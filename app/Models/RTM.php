<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RTM extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rtms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rtm_code',
        'title',
        'description',
        'tahun_akademik_id',
        'meeting_date',
        'start_time',
        'end_time',
        'location',
        'agenda',
        'discussion_points',
        'decisions',
        'minutes',
        'follow_up_plan',
        'status',
        'chairman_id',
        'secretary_id',
        'minutes_file',
        'attendance_file',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'meeting_date' => 'date',
    ];

    /**
     * Get the tahun akademik that owns the RTM.
     */
    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    /**
     * Get the user who is the chairman.
     */
    public function chairman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chairman_id');
    }

    /**
     * Get the user who is the secretary.
     */
    public function secretary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretary_id');
    }

    /**
     * Get the participants for this RTM.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'rtm_participants', 'rtm_id', 'user_id')
                    ->withPivot('role', 'is_present', 'notes')
                    ->withTimestamps();
    }

    /**
     * Get the action items for this RTM.
     */
    public function actionItems(): HasMany
    {
        return $this->hasMany(RTMActionItem::class, 'rtm_id');
    }

    /**
     * Check if RTM is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get the duration of the meeting in minutes.
     */
    public function getDuration(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        return $this->end_time->diffInMinutes($this->start_time);
    }

    /**
     * Scope a query to only include completed RTMs.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include upcoming RTMs.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', '!=', 'completed')
                     ->where('meeting_date', '>=', now()->toDateString());
    }
}
