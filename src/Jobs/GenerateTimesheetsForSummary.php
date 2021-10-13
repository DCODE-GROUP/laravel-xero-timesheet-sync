<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GenerateTimesheetsForSummary implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Collection $users;
    protected Collection $userIdsWithTimesheets;
    protected string $payrollPeriod;
    protected string $cacheKey;

    public function __construct(
        Collection $users,
        Collection $userIdsWithTimesheets,
        string $payrollCalender,
        string $payrollPeriod
    ) {
        $this->queue = config('laravel-xero-timesheet-sync.queue_name');

        $this->users = $users;
        $this->userIdsWithTimesheets = $userIdsWithTimesheets;
        $this->payrollPeriod = $payrollPeriod;
        $this->cacheKey = "laravel-timesheet-sync-summary-{$payrollCalender}-{$payrollPeriod}";
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        /**
         * Set the cache key
         */
        $this->setCacheKey();

        $usersToGenerate = $this->users->filter(function ($user) {
            return ! in_array($user->id, $this->userIdsWithTimesheets->pluck('xerotimeable_id')->toArray());
        });

        $usersToGenerate->each(function ($user) {
            GenerateUserTimesheet::dispatch($user, $this->payrollPeriod);
        });

        /**
         * Delete the cache key
         */
        $this->removeCacheKey();
    }

    private function setCacheKey()
    {
        Cache::put($this->cacheKey, true);
    }

    private function removeCacheKey()
    {
        Cache::forget($this->cacheKey);
    }
}
