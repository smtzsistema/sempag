<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;

class SyncController extends Controller
{
    public function index(Event $event)
    {
        return view('admin.sync.index', compact('event'));
    }
}
