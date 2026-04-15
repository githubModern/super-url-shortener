<?php
// © Atia Hegazy — atiaeno.com

use App\Jobs\AnonymizeIpJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Story 3.12: Run IP anonymization daily at 02:00
Schedule::job(new AnonymizeIpJob)->dailyAt('02:00');

// Update disposable email domains list weekly
Schedule::command('disposable:update')->weekly();
