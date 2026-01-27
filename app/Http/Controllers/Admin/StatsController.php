<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;

class StatsController extends Controller
{
    public function index(Event $event)
    {
        return view('admin.stats.index', compact('event'));
    }
}
