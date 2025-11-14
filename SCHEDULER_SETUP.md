# Laravel Scheduler Setup - SIM-PM

This document explains how to set up and configure the Laravel Task Scheduler for automated deadline reminders and notifications.

## Overview

The SIM-PM application uses Laravel's Task Scheduler to automatically:
- Check for approaching deadlines (7, 3, and 1 day before due date)
- Send notification reminders to users
- Clean up old read notifications

## Scheduled Tasks

### 1. Deadline Reminder Checker
- **Command**: `akreditasi:check-deadlines`
- **Schedule**: Daily at 08:00 AM
- **Purpose**: Checks all active periode akreditasi and sends deadline reminders
- **Features**:
  - Prevents overlapping executions
  - Runs in background
  - Sends email alert on failure

### 2. Notification Cleanup
- **Purpose**: Deletes read notifications older than 30 days
- **Schedule**: Weekly on Sundays at 02:00 AM
- **Benefit**: Keeps database clean and optimized

## Server Setup

### Production Server (Linux)

Add the following cron entry to your server:

```bash
# Edit crontab
crontab -e

# Add this line (replace /path/to/your/project with actual path)
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

**Example with actual path:**
```bash
* * * * * cd /home/user/sim-pm && php artisan schedule:run >> /dev/null 2>&1
```

### What This Does:
- Runs every minute (Laravel scheduler handles timing)
- Executes only tasks that are due
- Redirects output to /dev/null (silent execution)

### Verify Cron Setup

Check if cron entry exists:
```bash
crontab -l
```

## Development/Local Setup

### Option 1: Run Scheduler Manually (For Testing)
```bash
# Run all scheduled tasks immediately (for testing)
php artisan schedule:run

# List all scheduled tasks
php artisan schedule:list

# Run specific command manually
php artisan akreditasi:check-deadlines
```

### Option 2: Keep Scheduler Running (Development)
```bash
# Keep scheduler running in terminal (Ctrl+C to stop)
php artisan schedule:work
```

This runs `schedule:run` every minute automatically (no cron needed).

### Option 3: Use Laravel Sail (Docker)
If using Laravel Sail, add to `docker-compose.yml`:

```yaml
services:
  laravel.test:
    # ... existing config ...
    command: bash -c "php-fpm -D && php artisan schedule:work"
```

## Testing the Scheduler

### 1. Verify Schedule Configuration
```bash
php artisan schedule:list
```

Expected output:
```
0 8 * * * .......... akreditasi:check-deadlines
0 2 * * 0 .......... Closure
```

### 2. Test Deadline Checker Command
```bash
# Run command directly to test
php artisan akreditasi:check-deadlines

# Expected output:
# Checking for approaching deadlines...
# Processed X periode(s)
# Created Y notification(s)
# Deadline check completed successfully!
```

### 3. Test Scheduled Run
```bash
# Force run all scheduled tasks
php artisan schedule:run
```

### 4. Monitor Scheduler Logs
Laravel logs scheduled task output. Check logs:
```bash
tail -f storage/logs/laravel.log
```

## Schedule Configuration Details

### Deadline Checker Schedule
```php
$schedule->command('akreditasi:check-deadlines')
    ->dailyAt('08:00')           // Run at 8:00 AM every day
    ->withoutOverlapping()        // Prevent concurrent runs
    ->runInBackground()           // Don't block other tasks
    ->emailOutputOnFailure(...)   // Email admin on failure
```

### Alternative Schedules (Uncomment in Kernel.php)

**Every 6 hours** (for critical periods):
```php
$schedule->command('akreditasi:check-deadlines')
    ->everySixHours()  // 00:00, 06:00, 12:00, 18:00
    ->withoutOverlapping()
    ->runInBackground();
```

**Hourly during business hours**:
```php
$schedule->command('akreditasi:check-deadlines')
    ->hourly()
    ->between('8:00', '17:00')  // Only 8 AM to 5 PM
    ->weekdays()                 // Monday to Friday only
    ->withoutOverlapping();
```

## Email Configuration (Optional)

To receive failure notifications, configure admin email in `.env`:

```env
MAIL_ADMIN_EMAIL=admin@example.com
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@sim-pm.com
MAIL_FROM_NAME="SIM-PM Scheduler"
```

Then update `config/mail.php`:
```php
'admin_email' => env('MAIL_ADMIN_EMAIL', 'admin@example.com'),
```

## Troubleshooting

### Scheduler Not Running

**1. Check cron entry:**
```bash
crontab -l
```

**2. Check Laravel logs:**
```bash
tail -f storage/logs/laravel.log
```

**3. Verify PHP path:**
```bash
which php
# Use full path in crontab if needed:
# * * * * * /usr/bin/php /path/to/artisan schedule:run
```

**4. Check file permissions:**
```bash
# Ensure artisan is executable
chmod +x artisan

# Ensure scheduler can write logs
chmod -R 775 storage/logs
```

### Command Runs But Doesn't Work

**1. Test command manually:**
```bash
php artisan akreditasi:check-deadlines -v
```

**2. Check database connection:**
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

**3. Check notification service:**
```bash
php artisan tinker
>>> app(\App\Services\NotificationService::class)->checkDeadlineReminders();
```

### Performance Issues

If scheduler causes high server load:

**1. Reduce frequency:**
```php
// Instead of dailyAt, use twiceDaily
$schedule->command('akreditasi:check-deadlines')
    ->twiceDaily(8, 14);  // 8 AM and 2 PM
```

**2. Limit notifications:**
Edit `NotificationService::checkDeadlineReminders()` to process in batches.

**3. Use queue for notifications:**
```php
// In NotificationService
dispatch(new SendDeadlineNotification($user, $notification));
```

## Monitoring Best Practices

1. **Log all scheduler runs:**
   ```php
   $schedule->command('akreditasi:check-deadlines')
       ->dailyAt('08:00')
       ->appendOutputTo(storage_path('logs/deadline-checker.log'));
   ```

2. **Set up monitoring alerts** (e.g., using Laravel Telescope, Sentry, or custom monitoring)

3. **Check scheduler health regularly:**
   ```bash
   # Create health check command
   php artisan make:command CheckSchedulerHealth
   ```

4. **Review notification statistics weekly**

## Additional Resources

- [Laravel Task Scheduling Documentation](https://laravel.com/docs/10.x/scheduling)
- [Cron Expression Syntax](https://crontab.guru/)
- [Laravel Scheduler UI Package](https://github.com/codions/laravel-scheduler) (Optional)

## Support

For issues with scheduler setup, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Cron logs: `/var/log/syslog` or `/var/log/cron`
3. Server timezone: `date` command
4. PHP timezone: `php -i | grep date.timezone`

---

**Last Updated:** 2025-11-14
**Maintained By:** SIM-PM Development Team
