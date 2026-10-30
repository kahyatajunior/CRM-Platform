<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Opportunity;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function updateStage(Request $request, Opportunity $opportunity)
    {
        $validated = $request->validate([
            'stage' => ['required', 'string', 'max:80'],
        ]);

        $opportunity->update([
            'stage' => $validated['stage'],
            'probability' => $this->probabilityForStage($validated['stage']),
        ]);

        AuditLog::create([
            'model_type' => Opportunity::class,
            'model_id' => $opportunity->id,
            'event' => 'opportunity.stage_changed',
            'payload' => ['stage' => $validated['stage']],
        ]);

        return response()->json($opportunity->fresh());
    }

    protected function probabilityForStage(string $stage): int
    {
        return match ($stage) {
            'new' => 10,
            'proposal' => 60,
            'negotiation' => 75,
            'won' => 100,
            'lost' => 0,
            default => 25,
        };
    }
}
