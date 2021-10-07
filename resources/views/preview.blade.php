@extends(config('laravel-xero-timesheet-sync.admin_app_layout'))

@section('content')
<div>

    <div>
        <form action="{{ route('xero_timesheet_sync.preview') }}" method="GET">
            @csrf

            <div>
                <label for="payroll_calendar">@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.labels.payroll_calendar'): </label>
                <select name="payroll_calendar" id="payroll_calendar" onchange="this.form.submit()">
                    @foreach($xero_payroll_calendars as $calendar)
                        <option value="{{ data_get($calendar, 'PayrollCalendarID') }}">{{ data_get($calendar, 'Name') }}</option>
                    @endforeach
                </select>
                @error('payroll_calendar')
                <small>{{ $message }}</small>
                @enderror
            </div>

            <footer>
                <input type="submit" value="Sign up!" class="button success">
            </footer>

        </form>
    </div>

    <div>
        table here
    </div>

    @if (!empty($periodSelectOptions))

        {{ Form::open(['route' => ['admin.users.timesheets-summary', $user], 'method' => 'get']) }}
        {{ Form::label('period', 'Select a pay period') }}
        {{ Form::select('period', $periodSelectOptions, (isset($xeroTimesheet) ? $xeroTimesheet->id : null), [
        'data-selectize',
        'data-timesheets-summary-select',
        'placeholder' => 'Browse or search for a pay period']) }}
        {{ Form::close() }}

        @if (isset($leave) && $leave != null && $leave->count() > 0)
            <br />
            <hr />
            <h4>Leave within this Period:</h4>
            @foreach ($leave as $leaveItem)
                <div>{{ $leaveItem->leaveTypeName }} ({{ $leaveItem->startDate }} - {{ $leaveItem->stopDate }}) {{ $leaveItem->totalHours }}hours ({{ $leaveItem->leaveStatus }})</div>
            @endforeach
            <br />
            <br />
        @endif

        @isset($xeroTimesheet)
            <br />
            <h2>
                @lang('Total Hours For Week')
                {{ $xeroTimesheet->totalUnits }}
            </h2>
            {{ Form::open(['route' => ['admin.users.timesheets-summary.update-overrides', $user, $xeroTimesheet]]) }}
            <table>
                <thead>
                <tr>
                    <th></th>
                    @foreach($xeroTimesheet->payPeriodDays as $day)
                        <th>{{ $day->format('D jS') }} </th>
                    @endforeach
                    <th>@lang('Total')</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $totals = [];
                @endphp
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
            {{ Form::submit('Update timesheet overrides', ['class' => 'ee-button -med -solid-green']) }}
            {{ Form::close() }}

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