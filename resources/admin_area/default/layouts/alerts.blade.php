@if($success = Session::get('success'))
    <x-admin::alerts.success :message="$success"/>
@endif

@if($error = Session::get('error'))
    <x-admin::alerts.danger :message="$error"/>
@endif

@if($warning = Session::get('warning'))
    <x-admin::alerts.warning :message="$warning"/>
@endif

@if($info = Session::get('info'))
    <x-admin::alerts.info :message="$info"/>
@endif

@if(!utils()->checkStatus())
{{--    <x-admin::alerts.warning :message="__('messages.error_schedule_status', ['command' => '* * * * * php ' . base_path('artisan') . ' schedule:run >> /dev/null 2>&1'])"/>--}}
@endif
