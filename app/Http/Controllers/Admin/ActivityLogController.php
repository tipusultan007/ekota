<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        if ($request->filled('log_name')) {
            $query->where('log_name', 'like', '%' . $request->log_name . '%');
        }

        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        $activities = $query->paginate(50)->withQueryString();
        
        $users = \App\Models\User::all();

        return view('admin.activity_logs.index', compact('activities', 'users'));
    }

    public function show(Activity $activity)
    {
        return view('admin.activity_logs.show', compact('activity'));
    }
}
