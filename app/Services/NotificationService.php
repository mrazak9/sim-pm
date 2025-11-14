<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\PeriodeAkreditasi;
use App\Models\PengisianButir;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Create a new notification
     */
    public function create(array $data): Notification
    {
        return Notification::create($data);
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(int $userId, array $filters = [])
    {
        $query = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if (!$notification) {
            return false;
        }

        $notification->markAsRead();
        return true;
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Create deadline reminder notification
     */
    public function createDeadlineReminder(
        PeriodeAkreditasi $periode,
        User $user,
        int $daysLeft
    ): Notification {
        $priority = $this->determinePriority($daysLeft);

        $notification = $this->create([
            'type' => 'deadline_reminder',
            'notifiable_type' => PeriodeAkreditasi::class,
            'notifiable_id' => $periode->id,
            'user_id' => $user->id,
            'title' => 'Pengingat Deadline Akreditasi',
            'message' => sprintf(
                'Periode akreditasi "%s" akan berakhir dalam %d hari (%s)',
                $periode->nama,
                $daysLeft,
                Carbon::parse($periode->deadline_pengumpulan)->format('d M Y')
            ),
            'data' => [
                'periode_id' => $periode->id,
                'periode_nama' => $periode->nama,
                'deadline' => $periode->deadline_pengumpulan,
                'days_left' => $daysLeft,
            ],
            'priority' => $priority,
            'action_url' => "/akreditasi/periode/{$periode->id}",
        ]);

        // Send email notification
        $this->sendEmailNotification($notification);

        return $notification;
    }

    /**
     * Create butir deadline reminder notification
     */
    public function createButirDeadlineReminder(
        PengisianButir $pengisian,
        PeriodeAkreditasi $periode,
        int $daysLeft
    ): Notification {
        $priority = $this->determinePriority($daysLeft);

        $notification = $this->create([
            'type' => 'butir_deadline_reminder',
            'notifiable_type' => PengisianButir::class,
            'notifiable_id' => $pengisian->id,
            'user_id' => $pengisian->pic_user_id,
            'title' => 'Pengingat Pengisian Butir Akreditasi',
            'message' => sprintf(
                'Butir "%s" dalam periode "%s" harus diselesaikan. Deadline dalam %d hari!',
                $pengisian->butirAkreditasi->nama ?? 'N/A',
                $periode->nama,
                $daysLeft
            ),
            'data' => [
                'pengisian_id' => $pengisian->id,
                'butir_kode' => $pengisian->butirAkreditasi->kode ?? 'N/A',
                'butir_nama' => $pengisian->butirAkreditasi->nama ?? 'N/A',
                'periode_id' => $periode->id,
                'periode_nama' => $periode->nama,
                'deadline' => $periode->deadline_pengumpulan,
                'days_left' => $daysLeft,
                'status' => $pengisian->status,
                'completion' => $pengisian->completion_percentage,
            ],
            'priority' => $priority,
            'action_url' => "/akreditasi/pengisian/{$pengisian->id}/edit",
        ]);

        // Send email notification
        $this->sendEmailNotification($notification);

        return $notification;
    }

    /**
     * Create approval request notification
     */
    public function createApprovalRequest(
        PengisianButir $pengisian,
        User $reviewer
    ): Notification {
        $notification = $this->create([
            'type' => 'approval_request',
            'notifiable_type' => PengisianButir::class,
            'notifiable_id' => $pengisian->id,
            'user_id' => $reviewer->id,
            'title' => 'Permintaan Persetujuan Pengisian Butir',
            'message' => sprintf(
                'Pengisian butir "%s" telah diajukan oleh %s dan memerlukan review Anda',
                $pengisian->butirAkreditasi->nama ?? 'N/A',
                $pengisian->picUser->name ?? 'N/A'
            ),
            'data' => [
                'pengisian_id' => $pengisian->id,
                'butir_kode' => $pengisian->butirAkreditasi->kode ?? 'N/A',
                'butir_nama' => $pengisian->butirAkreditasi->nama ?? 'N/A',
                'pic_name' => $pengisian->picUser->name ?? 'N/A',
                'submitted_at' => now()->toDateTimeString(),
            ],
            'priority' => 'high',
            'action_url' => "/akreditasi/pengisian/{$pengisian->id}/edit",
        ]);

        // Send email notification
        $this->sendEmailNotification($notification);

        return $notification;
    }

    /**
     * Create status change notification
     */
    public function createStatusChange(
        PengisianButir $pengisian,
        string $oldStatus,
        string $newStatus
    ): Notification {
        $statusLabels = [
            'draft' => 'Draft',
            'submitted' => 'Diajukan',
            'review' => 'Dalam Review',
            'revision' => 'Perlu Revisi',
            'approved' => 'Disetujui',
        ];

        $notification = $this->create([
            'type' => 'status_change',
            'notifiable_type' => PengisianButir::class,
            'notifiable_id' => $pengisian->id,
            'user_id' => $pengisian->pic_user_id,
            'title' => 'Perubahan Status Pengisian Butir',
            'message' => sprintf(
                'Status pengisian butir "%s" berubah dari "%s" menjadi "%s"',
                $pengisian->butirAkreditasi->nama ?? 'N/A',
                $statusLabels[$oldStatus] ?? $oldStatus,
                $statusLabels[$newStatus] ?? $newStatus
            ),
            'data' => [
                'pengisian_id' => $pengisian->id,
                'butir_kode' => $pengisian->butirAkreditasi->kode ?? 'N/A',
                'butir_nama' => $pengisian->butirAkreditasi->nama ?? 'N/A',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'review_notes' => $pengisian->review_notes,
            ],
            'priority' => $newStatus === 'revision' ? 'high' : 'medium',
            'action_url' => "/akreditasi/pengisian/{$pengisian->id}/edit",
        ]);

        // Send email notification
        $this->sendEmailNotification($notification);

        return $notification;
    }

    /**
     * Send email notification
     */
    protected function sendEmailNotification(Notification $notification): void
    {
        try {
            $user = $notification->user;

            if (!$user || !$user->email) {
                return;
            }

            // Here you would send the actual email using Laravel Mail
            // For now, we'll just log it
            Log::info('Email notification sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'notification_id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
            ]);

            // Mark email as sent
            $notification->update([
                'email_sent' => true,
                'email_sent_at' => now(),
            ]);

            // TODO: Uncomment when email is configured
            /*
            Mail::to($user->email)->send(new \App\Mail\NotificationMail($notification));
            */
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Determine priority based on days left
     */
    protected function determinePriority(int $daysLeft): string
    {
        if ($daysLeft <= 1) {
            return 'urgent';
        } elseif ($daysLeft <= 3) {
            return 'high';
        } elseif ($daysLeft <= 7) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Check and create deadline reminders for all active periodes
     */
    public function checkDeadlineReminders(): array
    {
        $results = [
            'processed' => 0,
            'notifications_created' => 0,
            'errors' => 0,
        ];

        try {
            $periodes = PeriodeAkreditasi::with(['pengisianButirs.picUser', 'pengisianButirs.butirAkreditasi'])
                ->whereIn('status', ['pengisian', 'review'])
                ->whereNotNull('deadline_pengumpulan')
                ->where('deadline_pengumpulan', '>=', now())
                ->get();

            foreach ($periodes as $periode) {
                $results['processed']++;

                $deadline = Carbon::parse($periode->deadline_pengumpulan);
                $daysLeft = now()->diffInDays($deadline, false);

                // Send reminders at 7, 3, and 1 day(s) before deadline
                $reminderThresholds = [7, 3, 1];

                foreach ($reminderThresholds as $threshold) {
                    if ($daysLeft == $threshold) {
                        // Notify all users with incomplete pengisian
                        $incompletePengisian = $periode->pengisianButirs()
                            ->whereIn('status', ['draft', 'revision'])
                            ->get();

                        foreach ($incompletePengisian as $pengisian) {
                            if ($pengisian->pic_user_id) {
                                $this->createButirDeadlineReminder(
                                    $pengisian,
                                    $periode,
                                    $daysLeft
                                );
                                $results['notifications_created']++;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $results['errors']++;
            Log::error('Error in checkDeadlineReminders', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $results;
    }

    /**
     * Delete old read notifications
     */
    public function deleteOldNotifications(int $days = 30): int
    {
        return Notification::where('is_read', true)
            ->where('read_at', '<', now()->subDays($days))
            ->delete();
    }
}
