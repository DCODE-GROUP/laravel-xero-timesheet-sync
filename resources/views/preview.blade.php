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

                <h2>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.phrases.total_period_hours') {{ $calendarName }}</h2>

{{--               @dd($payroll_calendar_period_days)--}}
{{--               @dd($timesheets)--}}

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
                    @php
                        $totals = [];
                    @endphp

                    @foreach()

                    @endforeach

                    @foreach($xeroTimesheet->lines as $xeroTimesheetLine)
                        <tr>
                            <td>{{ $xeroTimesheetLine->earningsRateName }}</td>
                            @foreach($xeroTimesheetLine->units as $unit)
                                @php
                                    if (empty ($totals[$unit->date])) {
                                        $totals[$unit->date] = 0;
                                    }
                                    $totals[$unit->date] += ($unit->hasOverride ? $unit->override->units : $unit->units);
                                @endphp
                                <td valign="top">
                                    {{
                                        Form::number($unit->id, ($unit->hasOverride ? $unit->override->units : $unit->units),
                                        ['step' => 'any', 'min' => 0, 'style' => 'width: 75px; '.($unit->hasOverride ? 'border:1px solid red' : null )])
                                    }}
                                    @if ($unit->hasOverride)
                                        <button type="button" data-delete-override="{{ $unit->override->id }}"
                                                class="ee-button -sml -solid-red"
                                                title="Clear override">
                                            &times;
                                        </button>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                {{ $xeroTimesheetLine->totalUnits }}
                            </td>
                        </tr>
                    @endforeach

                    <!-- Totals -->
                    <tr>
                        <td>Total</td>
                        @foreach($xeroTimesheet->payPeriodDays as $day)
                            <td style="text-align:left;">{{ $totals[$day->timestamp] ?? '0' }} </td>
                        @endforeach
                        <td>{{ $xeroTimesheet->totalUnits }}</td>
                    </tr>

                    </tbody>
                </table>

            </form>

        </div>

        @isset($xeroTimesheet)
            <br />

            @foreach($xeroTimesheet->units as $unit)
                @if ($unit->hasOverride)
                    {{ Form::open(['route' => ['admin.users.timesheets-summary.delete-override', $user, $unit->override], 'method' => 'delete', 'data-unit-override' => $unit->override->id]) }}
                    {{ Form::close() }}
                @endif
            @endforeach

            @if ($xeroTimesheet->hasUnitOverrides)
                <br />
                <hr />
                <ul>
                    @foreach($xeroTimesheet->unitsWithOverrides as $unit)
                        <li>
                            {{ $unit->dateForHumans }} was {{ $unit->units }} set to {{ $unit->override->units }}
                            <br />
                            <small>Created by {{ $unit->override->created_by }}
                                on {{ $unit->override->created_at }}</small>
                            @if ($unit->override->updated_by)
                                <br />
                                <small>Updated by {{ $unit->override->updated_by }}
                                    on {{ $unit->override->updated_at }}</small>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

        @endisset

    @else

        <div class="no-result">
            <h3>There are currently no Xero timesheet records exported for this user</h3>
        </div>

    @endif

</div>
@endsection