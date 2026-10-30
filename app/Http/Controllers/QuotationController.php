<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ERPOrder;
use App\Models\Quotation;
use App\Models\QuotationLine;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'opportunity_id' => ['nullable', 'exists:opportunities,id'],
            'expire_in_days' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_name' => ['required', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $total = 0;
        foreach ($validated['lines'] as $line) {
            $total += $line['quantity'] * $line['unit_price'];
        }

        $quotation = Quotation::create([
            'customer_id' => $validated['customer_id'],
            'opportunity_id' => $validated['opportunity_id'] ?? null,
            'total_amount' => $total,
            'status' => 'draft',
            'expires_at' => now()->addDays($validated['expire_in_days'] ?? 15),
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['lines'] as $line) {
            QuotationLine::create([
                'quotation_id' => $quotation->id,
                'product_name' => $line['product_name'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
            ]);
        }

        AuditLog::create([
            'model_type' => Quotation::class,
            'model_id' => $quotation->id,
            'event' => 'quotation.created',
            'payload' => ['total_amount' => $total],
        ]);

        return response()->json($quotation->load('lines'), 201);
    }

    public function approve(Quotation $quotation)
    {
        $quotation->update([
            'status' => 'approved',
        ]);

        AuditLog::create([
            'model_type' => Quotation::class,
            'model_id' => $quotation->id,
            'event' => 'quotation.approved',
            'payload' => ['status' => 'approved'],
        ]);

        return response()->json($quotation->fresh('lines'));
    }

    public function convert(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'erp_type' => ['required', 'string', 'max:50'],
        ]);

        $erpOrder = ERPOrder::firstOrCreate(
            ['quotation_id' => $quotation->id],
            [
                'customer_id' => $quotation->customer_id,
                'reference' => 'ERP-' . strtoupper($validated['erp_type']) . '-' . $quotation->id,
                'amount' => $quotation->total_amount,
                'status' => 'converted',
            ]
        );

        $quotation->update([
            'status' => 'converted',
        ]);

        AuditLog::create([
            'model_type' => Quotation::class,
            'model_id' => $quotation->id,
            'event' => 'quotation.converted',
            'payload' => [
                'erp_type' => $validated['erp_type'],
                'erp_order_id' => $erpOrder->id,
            ],
        ]);

        return response()->json([
            'quotation' => $quotation->fresh('lines'),
            'erp_order' => $erpOrder,
        ]);
    }
}
