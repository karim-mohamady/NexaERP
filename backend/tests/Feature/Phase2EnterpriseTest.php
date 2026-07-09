<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Models\WorkflowRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2EnterpriseTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::where('email', 'admin@nexaerp.com')->firstOrFail();
    }

    public function test_admin_can_login_and_load_dashboard(): void
    {
        $login = $this->postJson('/api/auth/login', [
            'email' => 'admin@nexaerp.com',
            'password' => 'password',
        ]);

        $login->assertOk()->assertJsonStructure(['token', 'user', 'permissions', 'roles']);

        $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/dashboard/summary')
            ->assertOk()
            ->assertJsonStructure(['kpis', 'series', 'recent_invoices']);
    }

    public function test_customer_crud_writes_audit_log(): void
    {
        $company = Company::firstOrFail();

        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/customers', [
            'company_id' => $company->id,
            'name' => 'Phase 2 Customer',
            'email' => 'phase2@example.com',
            'status' => 'active',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('customers', ['email' => 'phase2@example.com']);
        $this->assertDatabaseHas('audit_logs', ['module' => 'customers', 'action' => 'create']);

        $customer = Customer::where('email', 'phase2@example.com')->firstOrFail();
        $this->actingAs($this->admin, 'sanctum')->putJson("/api/customers/{$customer->id}", ['phone' => '+15550123'])->assertOk();
        $this->assertDatabaseHas('audit_logs', ['module' => 'customers', 'action' => 'update']);
    }

    public function test_workflow_approval_can_be_approved(): void
    {
        $request = WorkflowRequest::firstOrFail();

        $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/approvals/inbox')
            ->assertOk();

        $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/approvals/{$request->id}/approve", ['comment' => 'Looks good'])
            ->assertOk()
            ->assertJsonPath('status', 'approved');

        $this->assertDatabaseHas('approvals', ['workflow_request_id' => $request->id, 'status' => 'approved']);
    }

    public function test_ai_fallback_and_exports_work(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/ai/chat', ['message' => 'Which products are at risk of stockout?'])
            ->assertOk()
            ->assertJsonStructure(['provider', 'answer', 'signals', 'recommendations']);

        $invoice = Invoice::firstOrFail();
        $this->actingAs($this->admin, 'sanctum')
            ->get("/api/exports/invoices/{$invoice->id}/pdf")
            ->assertOk();

        $this->actingAs($this->admin, 'sanctum')
            ->get('/api/exports/reports/sales/excel')
            ->assertOk();
    }
}
