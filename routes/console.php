<?php

use App\Enums\BlogStatus;
use App\Models\BlogPost;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Schedule::call(fn () => BlogPost::where('status', BlogStatus::Scheduled)->where('published_at', '<=', now())->update(['status' => BlogStatus::Published]))->name('publish-scheduled-blog-posts')->everyMinute()->withoutOverlapping();
