<?php


namespace Dcodegroup\LaravelXeroTimesheets;


use App\Models\Timesheet;
use App\Services\Xero\TimesheetService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportXeroTimesheet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Timesheet $timesheet;

    /**
     * ExportXeroTimesheet constructor.
     * @param Timesheet $timesheet
     */
    public function __construct(Timesheet $timesheet)
    {
        $this->queue = config('laravel-xero-timesheets.queue_name');
        $timesheet->xeroException()->delete();
        $this->timesheet = $timesheet;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = resolve(TimesheetService::class);
        $service->updateXeroTimesheet($this->timesheet);
    }
}