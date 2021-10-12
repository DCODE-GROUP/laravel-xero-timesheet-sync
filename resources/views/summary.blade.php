@extends(config('laravel-xero-timesheet-sync.admin_app_layout'))

@section('content')
<div>

    <h2>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.headings.summary')</h2>

    <div>
        <form action="{{ route('xero_timesheet_sync.summary') }}" method="GET">

            <div>
                <label for="payroll_calendar">@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.labels.payroll_calendar'): </label>
                <select name="payroll_calendar" id="payroll_calendar" onchange="this.form.submit()">
                    <option value="">@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.placeholders.select')</option>
                    @foreach($xeroPayrollCalendars as $calendar)
                        <option value="{{ data_get($calendar, 'PayrollCalendarID') }}" {{ data_get($calendar, 'PayrollCalendarID') == request('payroll_calendar') ? ' selected' : '' }}>{{ data_get($calendar, 'Name') }}</option>
                    @endforeach
                </select>
                @error('payroll_calendar')
                <small>{{ $message }}</small>
                @enderror
            </div>

            <div>
                <label for="payroll_calendar_period">@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.labels.payroll_calendar_period'): </label>
                <select name="payroll_calendar_period" id="payroll_calendar_period" onchange="this.form.submit()">
                    <option value="">@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.placeholders.select')</option>
                    @foreach($payrollCalendarPeriods as $period)
                        <option value="{{ data_get($period, 'value') }}" {{ data_get($period, 'value') == request('payroll_calendar_period') ? ' selected' : '' }}>{{ data_get($period, 'label') }}</option>
                    @endforeach
                </select>
                @error('payroll_calendar_period')
                <small>{{ $message }}</small>
                @enderror
            </div>

            <footer>
                <input type="submit" value="@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.buttons.preview_submit')" class="button success">
            </footer>

        </form>
    </div>

    <div>

        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>total</th>

                    @foreach($payrollCalendarPeriodDays as $day)
                        <th>{{ $day }}</th>
                    @endforeach

                    days
                </tr>
            </thead>
            <tbody>

            </tbody>
            <tfoot>
                <tr>
                    <td>blah here maybe export all</td>
                </tr>
            </tfoot>
        </table>

    </div>

</div>
@endsection