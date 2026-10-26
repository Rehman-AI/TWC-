<?php

use App\Jobs\RefreshOAuthTokensJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// -- Scheduled Jobs ---------------------------------------------------

// Refresh OAuth tokens every 30 minutes (before they expire)
Schedule::job(new RefreshOAuthTokensJob())->everyThirtyMinutes();

// Clean up old audit logs (retention period from config)
Schedule::command('model:prune', ['--model' => 'App\Models\AuditLog'])
    ->daily()
    ->at('03:00');