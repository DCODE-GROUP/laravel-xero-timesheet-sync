<?php

namespace Dcodegroup\LaravelXeroTimesheetSync;

use App\Models\Setting;
use App\Models\Timesheet;
use App\Services\Xero\PayrollCalendarService;
use App\Services\Xero\PayrollService;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\CarbonPeriod;
use Dcodegroup\LaravelXeroOauth\BaseXeroService;
use Illuminate\Database\Eloquent\Collection;
use XeroPHP\Models\PayrollAU\Timesheet as XeroAPITimesheet;
use XeroPHP\Models\PayrollAU\Timesheet\TimesheetLine;
use XeroPHP\Remote\Exception\BadRequestException;

class XeroTimesheetSyncService extends BaseXeroService
{

    /**
     * Updates xero timesheet for given $timesheet
     *
     * Timesheets in Xero are stored as a group of timesheet lines
     * given this, the timesheet itself is only used as a range selector in order to select a collection and generate
     * these lines lines contain the actual hour values for each included timesheet and are linked to a XeroTimesheet
     *
     * @param  Timesheet  $timesheet
     *
     * @return void
     */
    public function updateXeroTimesheet(Timesheet $timesheet)
    {
        /**
         * Only send to xero if the timehseet not linked to booking and the timesheet is approved
         */
        if (!$timesheet->canSendToXero()) {
            return;
        }

        try {
            // Validate employee
            $user = $timesheet->booking->user ?? $timesheet->user;
            if (!$user->isValidXeroEmployee()) {
                throw new Exception('Employee #' . $user->id . '/' . $user->email . ' does not have valid Xero employee data');
            }

            // Get calendar
            $payrollService = resolve(PayrollService::class);
            if (!($payRollCalendar = $payrollService->getDefaultPayrollCalendar()) || $payRollCalendar instanceof Exception) {
                throw new Exception('Unable to retrieve Xero payroll calendar data');
            }

            // Determine pay period dates from timesheet date
            $periodDates = PayrollCalendarService::getPayPeriodDatesForTimesheet($payRollCalendar, $timesheet);

            // Create xero timesheet model
            $xeroAPITimesheetModel = self::createXeroTimesheetModel($periodDates, $timesheet);

            // Create timesheet lines
            $timesheetLines = self::createXeroTimesheetLines($periodDates, $user, $timesheet->xeroTimesheet);

            // Terminate if we have no timesheet line results (nothing to export/update)
            if (empty($timesheetLines->timesheets)) {
                //throw new Exception('Nothing to export/update');
                return;
            }

            // Construct lines model
            $linesModel = [];

            foreach ($timesheetLines->lines as $line) {
                $linesModel[] = ['TimesheetLine' => $line['model']];
            }

            // Update existing
            if ($existingXeroTimesheet = $timesheet->xeroTimesheet ?: self::getCollatedTimesheetWithExistingXero($user, $periodDates)) {
                logger('updating existing timesheet');
                $guid = (object) [
                    'identifier' => 'TimesheetID',
                    'guid'       => $existingXeroTimesheet->xero_timesheet_id,
                ];

                $xeroAPITimesheet = $this->updateModel(XeroAPITimesheet::class, $guid, $xeroAPITimesheetModel->parameters, $linesModel);
            } else {
                // Create new
                logger('Creating a new timesheet object on xero');
                // TODO query Xero here for existing timesheets as failsafe
                $xeroAPITimesheet = $this->saveModel(XeroAPITimesheet::class, $xeroAPITimesheetModel->parameters, $linesModel);

                if ($xeroAPITimesheet instanceof BadRequestException) {
                    logger($xeroAPITimesheet->getMessage());
                    report($xeroAPITimesheet);

                    if ($xeroAPITimesheet->getCode() == 400) {
                        $guid = (object) [
                            'identifier' => 'TimesheetID',
                            'guid'       => $existingXeroTimesheet->xero_timesheet_id,
                        ];

                        $xeroAPITimesheet = $this->updateModel(XeroAPITimesheet::class, $guid, $xeroAPITimesheetModel->parameters, $linesModel);
                    }
                }
                logger(json_encode($xeroAPITimesheet));
            }

            // Update DB
            if ($xeroAPITimesheet instanceof XeroAPITimesheet) {
                self::createOrUpdateDBXeroTimesheet($timesheet, $xeroAPITimesheet, $timesheetLines);
            }
        } catch (Exception $e) {
            report($e);

            $this->updateOrCreateException($e, $timesheet);
        }
    }
    
}
