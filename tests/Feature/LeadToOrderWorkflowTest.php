<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadToOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_to_quotation_to_erp_order_workflow(): void
    {
        $user = User::factory()->create([
            'name' => 'Sales Manager',
            'email' => 'sales@example.com',
        ]);

        $leadResponse = $this->postJson('/api/v1/leads', [
            'first_name' => 'Jane',
            'last_name' => 'Mweemba',
            'company' => 'ZamTech Solutions',
            'email' => 'jane@zamtech.co.zm',
            'phone' => '+260955123456',
            'source' => 'website',
            'notes' => 'Interested in enterprise CRM platform.',
        ]);

        $leadResponse->assertStatus(201);

        $lead = Lead::query()->firstOrFail();
        $this->assertSame('new', $lead->status);

        $assignResponse = $this->postJson('/api/v1/leads/' . $lead->id . '/assign', [
            'user_id' => $user->id,
        ]);
        $assignResponse->assertStatus(200);

        $this->postJson('/api/v1/activities', [
            'user_id' => $user->id,
            'lead_id' => $lead->id,
            'type' => 'call',
            'notes' => 'Introductory call completed and needs assessment confirmed.',
        ])->assertStatus(201);

        $qualifyResponse = $this->postJson('/api/v1/leads/' . $lead->id . '/qualify', [
            'notes' => 'Budget confirmed and decision-maker engaged.',
        ]);
        $qualifyResponse->assertStatus(200);

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'qualified',
        ]);

        $convertResponse = $this->postJson('/api/v1/leads/' . $lead->id . '/convert', [
            'opportunity_title' => 'Enterprise CRM Deployment',
            'value' => 15000,
            'currency' => 'USD',
        ]);

        $convertResponse->assertStatus(200);

        $customer = Customer::query()->firstOrFail();
        $opportunity = Opportunity::query()->firstOrFail();

        $this->assertNotNull($customer);
        $this->assertSame($customer->id, $opportunity->customer_id);
        $this->assertSame('new', $opportunity->stage);

        $this->postJson('/api/v1/opportunities/' . $opportunity->id . '/stage', [
            'stage' => 'proposal',
        ])->assertStatus(200);

        $quotationResponse = $this->postJson('/api/v1/quotations', [
            'customer_id' => $customer->id,
            'opportunity_id' => $opportunity->id,
            'expire_in_days' => 15,
            'lines' => [
                [
                    'product_name' => 'CRM License',
                    'quantity' => 1,
                    'unit_price' => 15000,
                ],
            ],
            'notes' => 'Proposal for a modular enterprise CRM deployment.',
        ]);

        $quotationResponse->assertStatus(201);

        $quotation = Quotation::query()->firstOrFail();

        $this->postJson('/api/v1/quotations/' . $quotation->id . '/approve')->assertStatus(200);

        $converted = $this->postJson('/api/v1/quotations/' . $quotation->id . '/convert', [
            'erp_type' => 'contract',
        ]);
        $converted->assertStatus(200);

        $this->assertDatabaseHas('erp_orders', [
            'quotation_id' => $quotation->id,
            'status' => 'converted',
        ]);

        $this->get('/customers/' . $customer->id)
            ->assertOk()
            ->assertSee('Jane')
            ->assertSee('Enterprise CRM Deployment')
            ->assertSee('CRM License');
    }
}
