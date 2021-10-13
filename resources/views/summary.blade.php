@extends(config('laravel-xero-timesheet-sync.admin_app_layout'))

@section('content')
    <div>

        <h2>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.headings.summary')</h2>

        <div>
            <form action="{{ route('xero_timesheet_sync.summary') }}" method="GET">

                <div>
                    <label for="payroll_calendar">@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.labels.payroll_calendar')
                        : </label>
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
                    <label for="payroll_calendar_period">@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.labels.payroll_calendar_period')
                        : </label>
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
                    <input type="submit"
                           value="@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.buttons.preview_submit')"
                           class="button success">
                </footer>

            </form>
        </div>

        <div>

            <table >
                <thead>
                <tr>
                    <th>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.labels.user')</th>
                    <th>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.labels.message')</th>
                    <th>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.labels.action')</th>
                    <th>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.labels.units')</th>

                </tr>
                </thead>
                <tbody>

                @foreach($xeroTimesheets as $timesheet)
                    @php $xeroTimesheetLines = $timesheet->lines()->get(); @endphp
                    <tr>
                        <td>{{ $timesheet->xerotimeable->xero_employee_name }}</td>
                        <td>
                            @if($timesheet->isOutOfSyncWithXero())
                                <header class="alert warning">
                                    <div>
                                        <span>*</span>
                                        <small>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.alerts.warning_timesheet_changed')</small>
                                    </div>
                                </header>
                            @endif
                        </td>


                        <td class="actions">
                            <a href="{{ route('xero_timesheet_sync.preview', [
                                                'user_id' => $timesheet->xerotimeable->id,
                                                'payroll_calendar' => request('payroll_calendar'),
                                                'payroll_calendar_period' => request('payroll_calendar_period'),
                                            ])}}"
                            class="button">@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.buttons.go_to_preview')</a>

                        </td>
                        <td>

                            <table>
                                <thead>
                                <tr>
                                    <th>Rate</th>
                                    <th>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.words.total')</th>
                                    @foreach($payrollCalendarPeriodDays as $day)
                                        <th colspan="2">{{ $day }}</th>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    @foreach($payrollCalendarPeriodDays as $day)
                                        <th>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.labels.timesheet_unit')</th>
                                        <th>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.labels.override_unit')</th>
                                    @endforeach
                                </tr>
                                </thead>

                                @foreach($earningRates as $rate)
                                    <tr>
                                        <td>{{ $rate['name'] }}</td>
                                        <td>
                                            {{ $xeroTimesheetLines->where('earnings_rate_configuration_key', $rate['key'])->sum('units_override') }}
                                        </td>

                                        @foreach($payrollCalendarPeriodDays as $key => $value)
                                            <td>
                                                <small class="original-units">
                                                    {{ data_get($xeroTimesheetLines->where('summary_form_key', $rate['key'].'_'.$key)->first()->toArray(), 'units') }}
                                                </small>
                                            </td>
                                            <td>
                                                <small class="original-units">
                                                    {{ data_get($xeroTimesheetLines->where('summary_form_key', $rate['key'].'_'.$key)->first()->toArray(), 'units_override') }}
                                                </small>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </table>

                        </td>

                    </tr>
                @endforeach

                </tbody>
            </table>

        </div>

    </div>
@endsection