<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Jobs;

use App\Models\User;
use Dcodegroup\LaravelXeroTimesheetSync\Service\PayrollCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateUserTimesheet implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $payrollPeriod;
    protected User $user;

    public function __construct(User $user, string $payrollPeriod)
    {
        $this->queue = config('laravel-xero-timesheet-sync.queue_name');

        $this->payrollPeriod = $payrollPeriod;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = resolve(PayrollCalendarService::class);
        $service->findOrCreateXeroTimesheet($this->payrollPeriod, $this->user);
    }
}
