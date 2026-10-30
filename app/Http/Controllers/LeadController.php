<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index()
    {
        return response()->json(Lead::with(['customer', 'owner'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $lead = Lead::create([
            ...$validated,
            'status' => 'new',
        ]);

        AuditLog::create([
            'model_type' => Lead::class,
            'model_id' => $lead->id,
            'event' => 'lead.created',
            'payload' => $validated,
        ]);

        return response()->json($lead, 201);
    }

    public function assign(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $lead->update([
            'assigned_to_user_id' => $validated['user_id'],
            'status' => 'contacted',
        ]);

        AuditLog::create([
            'model_type' => Lead::class,
            'model_id' => $lead->id,
            'event' => 'lead.assigned',
            'payload' => ['assigned_to_user_id' => $validated['user_id']],
        ]);

        return response()->json($lead->fresh(['owner']));
    }

    public function qualify(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $lead->update([
            'status' => 'qualified',
            'qualified_at' => now(),
            'notes' => $validated['notes'] ?? $lead->notes,
        ]);

        AuditLog::create([
            'model_type' => Lead::class,
            'model_id' => $lead->id,
            'event' => 'lead.qualified',
            'payload' => $validated,
        ]);

        return response()->json($lead->fresh());
    }

    public function convert(Request $request, Lead $lead)
    {
        if ($lead->customer_id) {
            return response()->json(['message' => 'Lead has already been converted.'], 409);
        }

        $validated = $request->validate([
            'opportunity_title' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
        ]);

        $customer = Customer::firstOrCreate(
            ['email' => $lead->email],
            [
                'name' => trim($lead->first_name . ' ' . $lead->last_name),
                'company' => $lead->company,
                'phone' => $lead->phone,
                'status' => 'active',
                'source' => $lead->source,
            ]
        );

        $customer->update([
            'company' => $customer->company ?? $lead->company,
            'phone' => $customer->phone ?? $lead->phone,
        ]);

        $opportunity = Opportunity::create([
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
            'title' => $validated['opportunity_title'],
            'stage' => 'new',
            'value' => (float) $validated['value'],
            'currency' => $validated['currency'] ?? 'USD',
            'probability' => 10,
        ]);

        $lead->update([
            'customer_id' => $customer->id,
            'status' => 'converted',
            'converted_at' => now(),
        ]);

        AuditLog::create([
            'model_type' => Lead::class,
            'model_id' => $lead->id,
            'event' => 'lead.converted',
            'payload' => [
                'customer_id' => $customer->id,
                'opportunity_id' => $opportunity->id,
            ],
        ]);

        return response()->json([
            'lead' => $lead->fresh(),
            'customer' => $customer->fresh(),
            'opportunity' => $opportunity->fresh(),
        ]);
    }
}
