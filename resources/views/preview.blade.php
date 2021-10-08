@extends(config('laravel-xero-timesheet-sync.admin_app_layout'))

@section('content')
<div>

{{--    @dd($payroll_calendar_periods);--}}

    <div>
        <form action="{{ route('xero_timesheet_sync.preview') }}" method="GET">
{{--            @csrf--}}

            <div>
                <label for="payroll_calendar">@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.labels.payroll_calendar'): </label>
                <select name="payroll_calendar" id="payroll_calendar" onchange="this.form.submit()">
                    @foreach($xero_payroll_calendars as $calendar)
                        <option value="{{ data_get($calendar, 'PayrollCalendarID') }}" {{ data_get($calendar, 'PayrollCalendarID') == request('payroll_calendar') ? ' selected' : '' }}>{{ data_get($calendar, 'Name') }}</option>
                    @endforeach
                </select>
                @error('payroll_calendar')
                <small>{{ $message }}</small>
                @enderror
            </div>

            <div>
                <label for="payroll_calendar_period">@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.labels.payroll_calendar'): </label>
                <select name="payroll_calendar_period" id="payroll_calendar_period" onchange="this.form.submit()">
                    @foreach($payroll_calendar_periods as $period)
                        <option value="{{ data_get($period, 'value') }}" {{ data_get($period, 'value') == request('payroll_calendar_period') ? ' selected' : '' }}>{{ data_get($period, 'label') }}</option>
                    @endforeach
                </select>
                @error('payroll_calendar_period')
                <small>{{ $message }}</small>
                @enderror
            </div>

            <div>
                <label for="user_id">@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.labels.user'): </label>
                <select name="user_id" id="user_id" onchange="this.form.submit()">
                    @foreach($users as $user)
                        <option value="{{ data_get($user, 'value') }}" {{ data_get($user, 'value') == request('user_id') ? ' selected' : '' }}>{{ data_get($user, 'label') }}</option>
                    @endforeach
                </select>
                @error('user_id')
                <small>{{ $message }}</small>
                @enderror
            </div>

            <footer>
                <input type="submit" value="@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.buttons.preview_submit')" class="button success">
            </footer>

        </form>
    </div>

    @if ($displayPreview)

        <div>

            <form action="{{ route('xero_timesheet_sync.send-to-xero') }}" method="POST">
                @csrf

                <input type="hidden" name="payroll_calendar" value="{{ request('payroll_calendar') }}">
                <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                <input type="hidden" name="payroll_calendar_period" value="{{ request('payroll_calendar_period') }}">
                <input type="hidden" name="xero_timesheet_id" value="{{ $xeroTimesheet->id }}">

{{--                @dd($xeroTimesheet->lines->toArray())--}}

                <h2>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.phrases.total_period_hours') {{ $calendarName }}</h2>

{{--               @dd($payroll_calendar_period_days)--}}
{{--               @dd($timesheets)--}}
{{--               @dd($xeroTimesheetLines)--}}
{{--               @dd($xeroTimesheet->lines()->get()->toArray())--}}

{{--                <ul>--}}
{{--                    @foreach($xero)--}}
{{--                </ul>--}}

                <table>
                    <thead>
                    <tr>
                        <th></th>
                        @foreach($payroll_calendar_period_days as $day)
                            <th>{{ $day }} </th>
                        @endforeach
                        <th>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.words.total')</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($earningRates as $rate)
                        <tr>
                            <td>{{ $rate['name'] }}</td>
                            @foreach($payroll_calendar_period_days as $key => $value)
                                <td>
{{--                                    @dd($xeroTimesheetLines->where('summary_form_key', $rate['key'].'_'.$key)->first()->toArray())--}}
{{--                                    @dd(data_get($xeroTimesheetLines->where('summary_form_key', $rate['key'].'_'.$key)->toArray(), 'units'))--}}
                                    {{ data_get($xeroTimesheetLines->where('summary_form_key', $rate['key'].'_'.$key)->first()->toArray(), 'units') }}
{{--                                    {{ data_get($xeroTimesheetLines->where('summary_form_key', $rate['key'].'_'.$key)->first()->toArray(), 'units_override') }}--}}
                                    <input
                                            type="number"
                                            name="units_override_{{ $rate['key'] }}_{{ $key }}"
                                            step="0.1"
                                            value="{{ data_get($xeroTimesheetLines->where('summary_form_key', $rate['key'].'_'.$key)->first()->toArray(), 'units_override') }}">

                                    @error('units_override_'. $rate['key'] .' '. $key)
                                    <small>{{ $message }}</small>
                                    @enderror
                                </td>
                            @endforeach
                            <td>
                                {{ $xeroTimesheetLines->where('earnings_rate_configuration_key', $rate['key'])->sum('units_override') }}
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>

            </form>

            @else
                <p>no Please select the fields first</p>
                @endif

        </div>

</div>
@endsection