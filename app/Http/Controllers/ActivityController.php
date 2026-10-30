<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'lead_id' => ['required', 'exists:leads,id'],
            'type' => ['required', 'string', 'max:100'],
            'notes' => ['required', 'string'],
        ]);

        $activity = Activity::create($validated);

        AuditLog::create([
            'model_type' => Activity::class,
            'model_id' => $activity->id,
            'event' => 'activity.created',
            'payload' => $validated,
        ]);

        return response()->json($activity, 201);
    }
}
