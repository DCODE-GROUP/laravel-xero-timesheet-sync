<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Jobs;

//use App\Services\Xero\TimesheetService;
use Dcodegroup\LaravelXeroTimesheetSync\XeroTimesheetSyncPayrollService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportXeroTimesheet implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Model $timesheet;

    public function __construct(Model $timesheet)
    {
        $this->queue = config('laravel-xero-timesheet-sync.queue_name');
        //$timesheet->xeroException()->delete();
        $this->timesheet = $timesheet;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = resolve(XeroTimesheetSyncPayrollService::class);
        $service->updateXeroTimesheet($this->timesheet);
    }
}
