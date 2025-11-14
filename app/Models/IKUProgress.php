<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IKUProgress extends Model
{
    use HasFactory;

    protected $table = 'iku_progress';

    protected $fillable = [
        'iku_target_id',
        'tanggal_capaian',
        'nilai_capaian',
        'persentase_capaian',
        'keterangan',
        'bukti_dokumen',
        'created_by',
    ];

    protected $casts = [
        'tanggal_capaian' => 'date',
        'nilai_capaian' => 'decimal:2',
        'persentase_capaian' => 'decimal:2',
    ];

    /**
     * Get the target this progress belongs to
     */
    public function target()
    {
        return $this->belongsTo(IKUTarget::class, 'iku_target_id');
    }

    /**
     * Get the user who created this progress
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_capaian', [$startDate, $endDate]);
    }

    /**
     * Scope to get recent progress
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('tanggal_capaian', '>=', now()->subDays($days));
    }
}
