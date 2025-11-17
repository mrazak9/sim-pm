<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RTMParticipant extends Pivot
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rtm_id',
        'user_id',
        'role',
        'is_present',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_present' => 'boolean',
    ];

    /**
     * Get the RTM that owns the participant.
     */
    public function rtm(): BelongsTo
    {
        return $this->belongsTo(RTM::class);
    }

    /**
     * Get the user that is the participant.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
