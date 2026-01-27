<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;

class AttendanceController extends Controller
{
    public function index(Event $event)
    {
        return view('admin.attendance.index', compact('event'));
    }
}
