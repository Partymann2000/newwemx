<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppTaskLog;

class ScheduleLogController extends Controller
{
    public function index()
    {
        return view('admin::schedule-logs.index');
    }

    public function view(AppTaskLog $log)
    {
        return view('admin::schedule-logs.view', ['log' => $log]);
    }
}
