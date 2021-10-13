@extends(config('laravel-xero-timesheet-sync.admin_app_layout'))

@section('content')
<div>

    <h2>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.headings.generating')</h2>

    <div>
        <p>@lang('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.phrases.summary_generating')</p>
    </div>

    <script>
        function pageRedirect() {
            window.location ="{{ route('xero_timesheet_sync.summary') }}?payroll_calendar={{ $payroll_calendar }}&payroll_calendar_period={{ urlencode($payroll_calendar_period) }}";
        }
        setTimeout("pageRedirect()", 10000);
    </script>

</div>
@endsection