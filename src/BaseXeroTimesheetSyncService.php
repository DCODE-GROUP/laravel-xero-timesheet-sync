<?php

namespace Dcodegroup\LaravelXeroTimesheetSync;

use Dcodegroup\LaravelXeroOauth\BaseXeroService;
use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;
use XeroPHP\Models\PayrollAU\Timesheet;
use XeroPHP\Remote\Exception\BadRequestException;

class BaseXeroTimesheetSyncService extends BaseXeroService
{
    /**
     * @param  \Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet  $xeroTimesheet
     *
     * @return false|void
     */
    public function updateXeroTimesheet(XeroTimesheet $xeroTimesheet)
    {
        $timesheetParameters = [
            'EmployeeID' => $xeroTimesheet->xero_employee_id,
            'StartDate' => $xeroTimesheet->start_date,
            'EndDate' => $xeroTimesheet->end_date,
        ];

        if ($xeroTimesheet->hasXeroGuid()) {
            $response = $this->updateModel(
                Timesheet::class,
                (object) [
                    'identifier' => 'TimesheetID',
                    'guid' => $xeroTimesheet->xero_timesheet_guid,
                ],
                $timesheetParameters,
                $xeroTimesheet->prepareTimesheetLines()
            );
        } else {
            $response = $this->saveModel(
                Timesheet::class,
                $timesheetParameters,
                $xeroTimesheet->prepareTimesheetLines()
            );
        }

        //logger('response: '.json_encode($response));

        if ($response instanceof BadRequestException) {
            logger($response->getMessage());
            report($response);

            return false;
        }

        // Update DB
        if ($response instanceof Timesheet) {
            $xeroTimesheet->update([
                'xero_timesheet_guid' => $response->getTimesheetID() ?? null,
                'synced_at' => now(),
                'status' => $response->getStatus(),
                'hours' => $response->getHours(),
            ]);
        }
    }
}
