<?php

namespace Dcodegroup\LaravelXeroTimesheetSync;

use Dcodegroup\LaravelXeroOauth\BaseXeroService;
use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;
use XeroPHP\Models\PayrollAU\Timesheet;
use XeroPHP\Remote\Exception\BadRequestException;

class BaseXeroTimesheetSyncService extends BaseXeroService
{
    public function updateXeroTimesheet(XeroTimesheet $xeroTimesheet)
    {

        $timesheetParameters = [
        'EmployeeID' => $xeroTimesheet->xero_employee_id,
        'StartDate'  => $xeroTimesheet->start_date,
        'EndDate'    => $xeroTimesheet->end_date,
    ];

        if ($xeroTimesheet->hasXeroGuid()) {
            $response = $this->updateModel(Timesheet::class, $xeroTimesheet->xero_timesheet_guid, $timesheetParameters, $xeroTimesheet->prepareTimesheetLines());
        } else {
            $response = $this->saveModel(Timesheet::class, $timesheetParameters, $xeroTimesheet->prepareTimesheetLines());
        }

    }
}
