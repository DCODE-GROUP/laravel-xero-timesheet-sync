<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Jobs;

use Dcodegroup\LaravelXeroTimesheetSync\BaseXeroTimesheetSyncService;
use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTimesheetToXero implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected XeroTimesheet $xeroTimesheet;

    public function __construct(XeroTimesheet $xeroTimesheet)
    {
        $this->queue = config('laravel-xero-timesheet-sync.queue_name');
        $this->xeroTimesheet = $xeroTimesheet;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = resolve(BaseXeroTimesheetSyncService::class);
        $service->updateXeroTimesheet($this->xeroTimesheet);
    }
}
