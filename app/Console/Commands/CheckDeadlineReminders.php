<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckDeadlineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'akreditasi:check-deadlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for approaching deadlines and send reminder notifications';

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for approaching deadlines...');

        $results = $this->notificationService->checkDeadlineReminders();

        $this->info('Processed ' . $results['processed'] . ' periode(s)');
        $this->info('Created ' . $results['notifications_created'] . ' notification(s)');

        if ($results['errors'] > 0) {
            $this->error('Encountered ' . $results['errors'] . ' error(s)');
            return self::FAILURE;
        }

        $this->info('Deadline check completed successfully!');
        return self::SUCCESS;
    }
}
