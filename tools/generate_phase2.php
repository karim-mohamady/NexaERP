<?php

$root = dirname(__DIR__);

function put_file(string $path, string $contents): void
{
    $dir = dirname($path);
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    file_put_contents($path, $contents);
}

function backend(string $path): string
{
    global $root;
    return $root.'/backend/'.$path;
}

function frontend(string $path): string
{
    global $root;
    return $root.'/frontend/'.$path;
}

put_file(backend('database/migrations/2026_07_10_020000_create_phase2_enterprise_tables.php'), <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->string('module')->nullable()->index();
            $table->unsignedBigInteger('record_id')->nullable()->index();
            $table->string('user_agent')->nullable();
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->json('data')->nullable();
            $table->string('action_url')->nullable();
            $table->string('priority')->default('normal');
        });

        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('module');
            $table->string('trigger_type')->default('manual');
            $table->decimal('amount_threshold', 12, 2)->default(0);
            $table->string('required_role')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('required_role');
            $table->unsignedInteger('approval_order')->default(1);
            $table->boolean('is_final')->default(false);
            $table->timestamps();
        });

        Schema::create('workflow_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('workflow_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('module');
            $table->string('record_type')->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_request_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_step_id')->nullable()->constrained()->nullOnDelete();
            $table->string('required_role');
            $table->unsignedInteger('approval_order')->default(1);
            $table->string('status')->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('workflow_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module');
            $table->string('record_type')->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('status')->default('pending');
            $table->text('comment')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('saved_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('report_name');
            $table->string('module');
            $table->json('selected_columns')->nullable();
            $table->json('filters')->nullable();
            $table->string('group_by')->nullable();
            $table->string('sort_by')->nullable();
            $table->timestamps();
        });

        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('widget_type');
            $table->string('title');
            $table->json('config')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->string('size')->default('md');
            $table->timestamps();
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('monthly_price', 12, 2)->default(0);
            $table->json('features')->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('trial');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('usage_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('metric');
            $table->unsignedInteger('limit')->default(0);
            $table->unsignedInteger('used')->default(0);
            $table->timestamps();
        });

        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('status')->default('draft');
            $table->date('transfer_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_delta');
            $table->string('reason')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('stage')->default('qualified');
            $table->decimal('value', 12, 2)->default(0);
            $table->date('expected_close_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'deals', 'stock_adjustments', 'stock_transfers', 'cost_centers', 'usage_limits', 'subscriptions',
            'subscription_plans', 'tasks', 'attachments', 'dashboard_widgets', 'saved_reports', 'approvals',
            'workflow_request_steps', 'workflow_requests', 'workflow_steps', 'workflows',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
PHP);

$models = [
    'Workflow' => 'workflows',
    'WorkflowStep' => 'workflow_steps',
    'WorkflowRequest' => 'workflow_requests',
    'WorkflowRequestStep' => 'workflow_request_steps',
    'Approval' => 'approvals',
    'SavedReport' => 'saved_reports',
    'DashboardWidget' => 'dashboard_widgets',
    'Attachment' => 'attachments',
    'Task' => 'tasks',
    'SubscriptionPlan' => 'subscription_plans',
    'Subscription' => 'subscriptions',
    'UsageLimit' => 'usage_limits',
    'CostCenter' => 'cost_centers',
    'StockTransfer' => 'stock_transfers',
    'StockAdjustment' => 'stock_adjustments',
    'Deal' => 'deals',
];

foreach ($models as $model => $table) {
    put_file(backend("app/Models/{$model}.php"), <<<PHP
<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class {$model} extends Model
{
    use HasFactory;

    protected \$table = '{$table}';
    protected \$guarded = [];

    protected \$casts = [
        'config' => 'array',
        'data' => 'array',
        'features' => 'array',
        'filters' => 'array',
        'selected_columns' => 'array',
        'is_active' => 'boolean',
        'is_final' => 'boolean',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'acted_at' => 'datetime',
    ];
}
PHP);
}

put_file(backend('app/Services/AuditLogger.php'), <<<'PHP'
<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogger
{
    public function log(Request $request, string $module, string $action, mixed $recordId = null, array $old = [], array $new = []): void
    {
        $user = $request->user();

        AuditLog::create([
            'company_id' => $user?->company_id,
            'branch_id' => $user?->branch_id,
            'user_id' => $user?->id,
            'module' => $module,
            'action' => $action,
            'record_id' => is_numeric($recordId) ? (int) $recordId : null,
            'auditable_type' => $module,
            'auditable_id' => is_numeric($recordId) ? (int) $recordId : null,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }
}
PHP);

put_file(backend('app/Services/AiCopilotService.php'), <<<'PHP'
<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;

class AiCopilotService
{
    public function answer(int $companyId, string $question = ''): array
    {
        $revenue = (float) Invoice::where('company_id', $companyId)->sum('grand_total');
        $expenses = (float) Expense::where('company_id', $companyId)->sum('amount');
        $lowStock = Product::where('company_id', $companyId)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->get();
        $topCustomers = Customer::where('company_id', $companyId)->orderByDesc('balance')->limit(3)->pluck('name')->values();

        return [
            'provider' => env('OPENAI_API_KEY') ? env('AI_PROVIDER', 'openai') : 'local-rules',
            'answer' => $this->ruleBasedAnswer($question, $revenue, $expenses, $lowStock->count(), $topCustomers->all()),
            'signals' => [
                'revenue' => round($revenue, 2),
                'expenses' => round($expenses, 2),
                'profit' => round($revenue - $expenses, 2),
                'low_stock_count' => $lowStock->count(),
                'top_customers' => $topCustomers,
            ],
            'recommendations' => [
                'Prioritize overdue invoice collection before approving discretionary expenses.',
                'Create replenishment purchase orders for low-stock products.',
                'Review high-value customers and schedule follow-up tasks this week.',
            ],
        ];
    }

    private function ruleBasedAnswer(string $question, float $revenue, float $expenses, int $lowStockCount, array $topCustomers): string
    {
        $question = strtolower($question);
        if (str_contains($question, 'stock') || str_contains($question, 'inventory')) {
            return $lowStockCount > 0
                ? "{$lowStockCount} products are at stockout risk. Reorder them before approving new sales commitments."
                : 'Inventory risk is currently low based on configured thresholds.';
        }

        if (str_contains($question, 'expense') || str_contains($question, 'unusual')) {
            return $expenses > ($revenue * 0.5)
                ? 'Expenses are elevated versus revenue. Audit payroll, rent, software, and logistics before month end.'
                : 'No major expense anomaly is visible in the current seeded data.';
        }

        if (str_contains($question, 'customer')) {
            return 'The most valuable customer segments should be built around: '.implode(', ', $topCustomers ?: ['active enterprise customers']).'.';
        }

        return 'Focus this week on approvals, collections, low-stock replenishment, and follow-up tasks for high-value customers.';
    }
}
PHP);

put_file(backend('app/Http/Controllers/Api/WorkflowController.php'), <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Workflow::with('steps')->where('company_id', $request->user()->company_id)->latest()->paginate(15));
    }

    public function store(Request $request, AuditLogger $audit)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'module' => ['required', 'string', 'max:100'],
            'trigger_type' => ['nullable', 'string', 'max:100'],
            'amount_threshold' => ['nullable', 'numeric'],
            'required_role' => ['nullable', 'string', 'max:100'],
            'steps' => ['nullable', 'array'],
        ]);

        $workflow = Workflow::create([
            ...collect($data)->except('steps')->toArray(),
            'company_id' => $request->user()->company_id,
            'branch_id' => $request->user()->branch_id,
            'is_active' => true,
        ]);

        foreach (($data['steps'] ?? [['name' => 'Manager approval', 'required_role' => $data['required_role'] ?? 'Manager']]) as $index => $step) {
            WorkflowStep::create([
                'workflow_id' => $workflow->id,
                'name' => $step['name'] ?? 'Approval step',
                'required_role' => $step['required_role'] ?? 'Manager',
                'approval_order' => $step['approval_order'] ?? ($index + 1),
                'is_final' => $index === count($data['steps'] ?? [1]) - 1,
            ]);
        }

        $audit->log($request, 'workflows', 'create', $workflow->id, [], $workflow->toArray());

        return response()->json($workflow->load('steps'), 201);
    }

    public function update(Request $request, Workflow $workflow, AuditLogger $audit)
    {
        $old = $workflow->toArray();
        $workflow->update($request->only(['name', 'module', 'trigger_type', 'amount_threshold', 'required_role', 'status', 'is_active']));
        $audit->log($request, 'workflows', 'update', $workflow->id, $old, $workflow->fresh()->toArray());

        return response()->json($workflow->fresh('steps'));
    }

    public function destroy(Request $request, Workflow $workflow, AuditLogger $audit)
    {
        $old = $workflow->toArray();
        $workflow->delete();
        $audit->log($request, 'workflows', 'delete', $workflow->id, $old, []);

        return response()->json(['message' => 'Workflow deleted']);
    }
}
PHP);

put_file(backend('app/Http/Controllers/Api/ApprovalController.php'), <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\Notification;
use App\Models\Workflow;
use App\Models\WorkflowRequest;
use App\Models\WorkflowRequestStep;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function inbox(Request $request)
    {
        $roles = $request->user()->roles->pluck('name')->all();

        return response()->json(WorkflowRequest::query()
            ->with(['steps'])
            ->where('company_id', $request->user()->company_id)
            ->where('status', 'pending')
            ->whereHas('steps', fn ($query) => $query->where('status', 'pending')->whereIn('required_role', $roles))
            ->latest()
            ->paginate(15));
    }

    public function myRequests(Request $request)
    {
        return response()->json(WorkflowRequest::with('steps')->where('requested_by', $request->user()->id)->latest()->paginate(15));
    }

    public function show(Request $request, WorkflowRequest $workflowRequest)
    {
        abort_unless($workflowRequest->company_id === $request->user()->company_id, 403);

        return response()->json($workflowRequest->load('steps'));
    }

    public function submit(Request $request, AuditLogger $audit)
    {
        $data = $request->validate([
            'module' => ['required', 'string'],
            'record_type' => ['nullable', 'string'],
            'record_id' => ['nullable', 'integer'],
            'amount' => ['nullable', 'numeric'],
            'comments' => ['nullable', 'string'],
        ]);

        $workflow = Workflow::with('steps')
            ->where('company_id', $request->user()->company_id)
            ->where('module', $data['module'])
            ->where('is_active', true)
            ->where('amount_threshold', '<=', $data['amount'] ?? 0)
            ->orderByDesc('amount_threshold')
            ->first();

        $workflow ??= Workflow::with('steps')->where('company_id', $request->user()->company_id)->where('is_active', true)->first();

        $approvalRequest = WorkflowRequest::create([
            'company_id' => $request->user()->company_id,
            'branch_id' => $request->user()->branch_id,
            'workflow_id' => $workflow?->id,
            'requested_by' => $request->user()->id,
            'module' => $data['module'],
            'record_type' => $data['record_type'] ?? null,
            'record_id' => $data['record_id'] ?? null,
            'amount' => $data['amount'] ?? 0,
            'comments' => $data['comments'] ?? null,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $steps = $workflow?->steps;
        if (! $steps || $steps->isEmpty()) {
            $steps = collect([(object) ['id' => null, 'required_role' => 'Manager', 'approval_order' => 1]]);
        }

        foreach ($steps as $step) {
            WorkflowRequestStep::create([
                'workflow_request_id' => $approvalRequest->id,
                'workflow_step_id' => $step->id ?? null,
                'required_role' => $step->required_role,
                'approval_order' => $step->approval_order,
                'status' => 'pending',
            ]);
        }

        Notification::create([
            'company_id' => $request->user()->company_id,
            'title' => 'Approval request submitted',
            'body' => "{$data['module']} requires approval.",
            'type' => 'approval request',
            'priority' => 'high',
            'action_url' => '/app/approvals',
            'data' => ['workflow_request_id' => $approvalRequest->id],
        ]);

        $audit->log($request, 'approvals', 'submit', $approvalRequest->id, [], $approvalRequest->toArray());

        return response()->json($approvalRequest->load('steps'), 201);
    }

    public function approve(Request $request, WorkflowRequest $workflowRequest, AuditLogger $audit)
    {
        return $this->act($request, $workflowRequest, 'approved', $audit);
    }

    public function reject(Request $request, WorkflowRequest $workflowRequest, AuditLogger $audit)
    {
        return $this->act($request, $workflowRequest, 'rejected', $audit);
    }

    public function returnForRevision(Request $request, WorkflowRequest $workflowRequest, AuditLogger $audit)
    {
        return $this->act($request, $workflowRequest, 'returned', $audit);
    }

    public function cancel(Request $request, WorkflowRequest $workflowRequest, AuditLogger $audit)
    {
        return $this->act($request, $workflowRequest, 'cancelled', $audit);
    }

    private function act(Request $request, WorkflowRequest $workflowRequest, string $status, AuditLogger $audit)
    {
        abort_unless($workflowRequest->company_id === $request->user()->company_id, 403);
        $data = $request->validate(['comment' => ['nullable', 'string']]);
        $old = $workflowRequest->toArray();

        $workflowRequest->update([
            'status' => $status,
            'completed_at' => in_array($status, ['approved', 'rejected', 'cancelled'], true) ? now() : null,
        ]);

        $workflowRequest->steps()->where('status', 'pending')->orderBy('approval_order')->limit(1)->update([
            'status' => $status,
            'acted_by' => $request->user()->id,
            'acted_at' => now(),
            'comments' => $data['comment'] ?? null,
        ]);

        Approval::create([
            'company_id' => $request->user()->company_id,
            'branch_id' => $request->user()->branch_id,
            'workflow_request_id' => $workflowRequest->id,
            'module' => $workflowRequest->module,
            'record_type' => $workflowRequest->record_type,
            'record_id' => $workflowRequest->record_id,
            'status' => $status,
            'comment' => $data['comment'] ?? null,
            'approved_by' => $status === 'approved' ? $request->user()->id : null,
            'rejected_by' => $status === 'rejected' ? $request->user()->id : null,
            'acted_at' => now(),
        ]);

        $audit->log($request, 'approvals', $status, $workflowRequest->id, $old, $workflowRequest->fresh()->toArray());

        return response()->json($workflowRequest->fresh('steps'));
    }
}
PHP);

put_file(backend('app/Http/Controllers/Api/AuditLogController.php'), <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()->where('company_id', $request->user()->company_id)->with('user');

        foreach (['module', 'action', 'user_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function show(Request $request, AuditLog $auditLog)
    {
        abort_unless($auditLog->company_id === $request->user()->company_id, 403);

        return response()->json($auditLog->load('user'));
    }
}
PHP);

put_file(backend('app/Http/Controllers/Api/NotificationController.php'), <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::where('company_id', $request->user()->company_id)
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $request->user()->id));

        return response()->json([
            'unread' => (clone $query)->whereNull('read_at')->count(),
            'data' => $query->latest()->limit(20)->get(),
        ]);
    }

    public function markRead(Request $request, Notification $notification)
    {
        abort_unless($notification->company_id === $request->user()->company_id, 403);
        $notification->update(['read_at' => now()]);

        return response()->json($notification);
    }

    public function markAllRead(Request $request)
    {
        Notification::where('company_id', $request->user()->company_id)
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $request->user()->id))
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function preferences(Request $request)
    {
        $setting = Setting::firstOrCreate(
            ['company_id' => $request->user()->company_id, 'group' => 'notifications', 'key' => 'preferences'],
            ['value' => ['approval' => true, 'inventory' => true, 'ai' => true]]
        );

        return response()->json($setting);
    }
}
PHP);

put_file(backend('app/Http/Controllers/Api/ExportController.php'), <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Services\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function invoicePdf(Request $request, Invoice $invoice, AuditLogger $audit)
    {
        abort_unless($invoice->company_id === $request->user()->company_id, 403);
        $audit->log($request, 'invoices', 'export_pdf', $invoice->id);

        return Pdf::loadView('exports.invoice', ['document' => $invoice->load('customer'), 'title' => 'Invoice'])->download("invoice-{$invoice->number}.pdf");
    }

    public function quotationPdf(Request $request, Quotation $quotation, AuditLogger $audit)
    {
        abort_unless($quotation->company_id === $request->user()->company_id, 403);
        $audit->log($request, 'quotations', 'export_pdf', $quotation->id);

        return Pdf::loadView('exports.invoice', ['document' => $quotation->load('customer'), 'title' => 'Quotation'])->download("quotation-{$quotation->number}.pdf");
    }

    public function purchaseOrderPdf(Request $request, PurchaseOrder $purchaseOrder, AuditLogger $audit)
    {
        abort_unless($purchaseOrder->company_id === $request->user()->company_id, 403);
        $audit->log($request, 'purchase-orders', 'export_pdf', $purchaseOrder->id);

        return Pdf::loadView('exports.invoice', ['document' => $purchaseOrder->load('supplier'), 'title' => 'Purchase Order'])->download("purchase-order-{$purchaseOrder->number}.pdf");
    }

    public function reportExcel(Request $request, string $type, AuditLogger $audit): StreamedResponse
    {
        $companyId = $request->user()->company_id;
        $rows = match ($type) {
            'inventory' => Product::where('company_id', $companyId)->get(['sku', 'name', 'stock_quantity', 'cost_price', 'sale_price'])->toArray(),
            'profit-loss' => [['income' => Invoice::where('company_id', $companyId)->sum('grand_total'), 'expenses' => 0, 'net_profit' => Invoice::where('company_id', $companyId)->sum('grand_total')]],
            default => Invoice::where('company_id', $companyId)->get(['number', 'invoice_date', 'status', 'grand_total', 'paid_total'])->toArray(),
        };

        $audit->log($request, 'reports', 'export_excel', null, [], ['type' => $type]);

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            if ($rows !== []) {
                fputcsv($handle, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($handle, $row);
                }
            }
            fclose($handle);
        }, "{$type}-report.csv", ['Content-Type' => 'text/csv']);
    }
}
PHP);

put_file(backend('resources/views/exports/invoice.blade.php'), <<<'BLADE'
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #06b6d4; padding-bottom: 20px; margin-bottom: 28px; }
        .brand { font-size: 28px; font-weight: 800; }
        .muted { color: #64748b; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #f8fafc; }
        .total { text-align: right; font-size: 22px; font-weight: 800; margin-top: 28px; }
        .signature { margin-top: 70px; display: flex; justify-content: space-between; }
        .line { border-top: 1px solid #94a3b8; width: 220px; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">NexaERP</div>
            <div class="muted">Professional enterprise document</div>
        </div>
        <div>
            <h1>{{ $title }}</h1>
            <div class="muted">Number: {{ $document->number ?? 'DRAFT' }}</div>
            <div class="muted">Status: {{ $document->status ?? 'draft' }}</div>
        </div>
    </div>
    <p><strong>Customer/Supplier:</strong> {{ $document->customer->name ?? $document->supplier->name ?? 'N/A' }}</p>
    <p><strong>Date:</strong> {{ $document->invoice_date ?? $document->order_date ?? $document->quote_date ?? now()->toDateString() }}</p>
    <table>
        <thead><tr><th>Description</th><th>Subtotal</th><th>Tax</th><th>Discount</th><th>Total</th></tr></thead>
        <tbody>
            <tr>
                <td>{{ $title }} services and products</td>
                <td>{{ number_format((float) ($document->subtotal ?? $document->grand_total ?? 0), 2) }}</td>
                <td>{{ number_format((float) ($document->tax_total ?? 0), 2) }}</td>
                <td>{{ number_format((float) ($document->discount_total ?? 0), 2) }}</td>
                <td>{{ number_format((float) ($document->grand_total ?? 0), 2) }}</td>
            </tr>
        </tbody>
    </table>
    <div class="total">Grand Total: {{ number_format((float) ($document->grand_total ?? 0), 2) }}</div>
    <div class="signature">
        <div class="line">Prepared by</div>
        <div class="line">Approved by</div>
    </div>
</body>
</html>
BLADE);

put_file(backend('app/Http/Controllers/Api/EnterpriseDataController.php'), <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\CostCenter;
use App\Models\DashboardWidget;
use App\Models\Deal;
use App\Models\SavedReport;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Task;
use App\Models\UsageLimit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class EnterpriseDataController extends Controller
{
    private array $map = [
        'saved-reports' => SavedReport::class,
        'dashboard-widgets' => DashboardWidget::class,
        'attachments' => Attachment::class,
        'tasks' => Task::class,
        'cost-centers' => CostCenter::class,
        'stock-transfers' => StockTransfer::class,
        'stock-adjustments' => StockAdjustment::class,
        'deals' => Deal::class,
        'subscription-plans' => SubscriptionPlan::class,
        'subscriptions' => Subscription::class,
        'usage-limits' => UsageLimit::class,
    ];

    public function index(Request $request, string $resource)
    {
        $model = $this->model($resource);
        $query = $model::query();
        if ((new $model())->getConnection()->getSchemaBuilder()->hasColumn((new $model())->getTable(), 'company_id')) {
            $query->where('company_id', $request->user()->company_id);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request, string $resource)
    {
        $model = $this->model($resource);
        $payload = collect($request->all())->except(['id', 'created_at', 'updated_at'])->toArray();
        if ((new $model())->getConnection()->getSchemaBuilder()->hasColumn((new $model())->getTable(), 'company_id')) {
            $payload['company_id'] = $request->user()->company_id;
        }
        if ((new $model())->getTable() === 'dashboard_widgets') {
            $payload['user_id'] ??= $request->user()->id;
        }
        if ((new $model())->getTable() === 'saved_reports') {
            $payload['owner_id'] ??= $request->user()->id;
        }

        /** @var Model $record */
        $record = $model::create($payload);

        return response()->json($record, 201);
    }

    public function update(Request $request, string $resource, int $id)
    {
        $model = $this->model($resource);
        $record = $model::findOrFail($id);
        $record->update($request->all());

        return response()->json($record->fresh());
    }

    public function destroy(string $resource, int $id)
    {
        $model = $this->model($resource);
        $model::findOrFail($id)->delete();

        return response()->json(['message' => 'Deleted']);
    }

    private function model(string $resource): string
    {
        abort_unless(isset($this->map[$resource]), 404);

        return $this->map[$resource];
    }
}
PHP);

put_file(backend('app/Http/Controllers/Api/AiCopilotController.php'), <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiCopilotService;
use Illuminate\Http\Request;

class AiCopilotController extends Controller
{
    public function chat(Request $request, AiCopilotService $copilot)
    {
        $data = $request->validate(['message' => ['nullable', 'string', 'max:2000']]);

        return response()->json($copilot->answer($request->user()->company_id, $data['message'] ?? ''));
    }

    public function salesAnalysis(Request $request, AiCopilotService $copilot) { return response()->json($copilot->answer($request->user()->company_id, 'sales decrease')); }
    public function inventoryRisk(Request $request, AiCopilotService $copilot) { return response()->json($copilot->answer($request->user()->company_id, 'inventory stock risk')); }
    public function expenseAnomalies(Request $request, AiCopilotService $copilot) { return response()->json($copilot->answer($request->user()->company_id, 'expense anomalies')); }
    public function customerSegments(Request $request, AiCopilotService $copilot) { return response()->json($copilot->answer($request->user()->company_id, 'customer segments')); }
    public function forecast(Request $request, AiCopilotService $copilot) { return response()->json($copilot->answer($request->user()->company_id, 'forecast this month')); }
}
PHP);

put_file(backend('app/Http/Controllers/Api/AccountingInsightController.php'), <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Invoice;
use Illuminate\Http\Request;

class AccountingInsightController extends Controller
{
    public function trialBalance(Request $request)
    {
        return response()->json(Account::where('company_id', $request->user()->company_id)->get()->map(fn ($account) => [
            'code' => $account->code,
            'name' => $account->name,
            'type' => $account->type,
            'debit' => in_array($account->type, ['asset', 'expense'], true) ? (float) $account->opening_balance : 0,
            'credit' => in_array($account->type, ['liability', 'equity', 'income'], true) ? (float) $account->opening_balance : 0,
        ]));
    }

    public function profitLoss(Request $request)
    {
        $income = (float) Invoice::where('company_id', $request->user()->company_id)->sum('grand_total');
        $expenses = (float) Expense::where('company_id', $request->user()->company_id)->sum('amount');

        return response()->json(['income' => $income, 'expenses' => $expenses, 'net_profit' => $income - $expenses]);
    }

    public function cashBank(Request $request)
    {
        return response()->json(Account::where('company_id', $request->user()->company_id)->where('is_cash_bank', true)->get());
    }
}
PHP);

// Patch model relationships used by controllers.
file_put_contents(backend('app/Models/Workflow.php'), str_replace(
    "}\n",
    "    public function steps()\n    {\n        return \$this->hasMany(WorkflowStep::class)->orderBy('approval_order');\n    }\n}\n",
    file_get_contents(backend('app/Models/Workflow.php'))
));

file_put_contents(backend('app/Models/WorkflowRequest.php'), str_replace(
    "}\n",
    "    public function steps()\n    {\n        return \$this->hasMany(WorkflowRequestStep::class);\n    }\n}\n",
    file_get_contents(backend('app/Models/WorkflowRequest.php'))
));

put_file(backend('routes/api.php'), preg_replace(
    '/use Illuminate\\\\Support\\\\Facades\\\\Route;\n/',
    "use App\\Http\\Controllers\\Api\\AccountingInsightController;\nuse App\\Http\\Controllers\\Api\\AiCopilotController;\nuse App\\Http\\Controllers\\Api\\ApprovalController;\nuse App\\Http\\Controllers\\Api\\AuditLogController;\nuse App\\Http\\Controllers\\Api\\EnterpriseDataController;\nuse App\\Http\\Controllers\\Api\\ExportController;\nuse App\\Http\\Controllers\\Api\\NotificationController;\nuse App\\Http\\Controllers\\Api\\WorkflowController;\nuse Illuminate\\Support\\Facades\\Route;\n",
    file_get_contents(backend('routes/api.php'))
));

$routes = <<<'PHP'
    Route::apiResource('workflows', WorkflowController::class)->except(['show']);
    Route::get('approvals/inbox', [ApprovalController::class, 'inbox']);
    Route::get('approvals/my-requests', [ApprovalController::class, 'myRequests']);
    Route::get('approvals/{workflowRequest}', [ApprovalController::class, 'show']);
    Route::post('approvals/submit', [ApprovalController::class, 'submit']);
    Route::post('approvals/{workflowRequest}/approve', [ApprovalController::class, 'approve']);
    Route::post('approvals/{workflowRequest}/reject', [ApprovalController::class, 'reject']);
    Route::post('approvals/{workflowRequest}/return', [ApprovalController::class, 'returnForRevision']);
    Route::post('approvals/{workflowRequest}/cancel', [ApprovalController::class, 'cancel']);
    Route::get('audit-logs', [AuditLogController::class, 'index']);
    Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show']);
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::get('notification-preferences', [NotificationController::class, 'preferences']);
    Route::get('exports/invoices/{invoice}/pdf', [ExportController::class, 'invoicePdf']);
    Route::get('exports/quotations/{quotation}/pdf', [ExportController::class, 'quotationPdf']);
    Route::get('exports/purchase-orders/{purchaseOrder}/pdf', [ExportController::class, 'purchaseOrderPdf']);
    Route::get('exports/reports/{type}/excel', [ExportController::class, 'reportExcel']);
    Route::get('accounting/trial-balance', [AccountingInsightController::class, 'trialBalance']);
    Route::get('accounting/profit-loss-advanced', [AccountingInsightController::class, 'profitLoss']);
    Route::get('accounting/cash-bank', [AccountingInsightController::class, 'cashBank']);
    Route::post('ai/chat', [AiCopilotController::class, 'chat']);
    Route::get('ai/sales-analysis', [AiCopilotController::class, 'salesAnalysis']);
    Route::get('ai/inventory-risk', [AiCopilotController::class, 'inventoryRisk']);
    Route::get('ai/expense-anomalies', [AiCopilotController::class, 'expenseAnomalies']);
    Route::get('ai/customer-segments', [AiCopilotController::class, 'customerSegments']);
    Route::get('ai/forecast', [AiCopilotController::class, 'forecast']);
    Route::get('enterprise/{resource}', [EnterpriseDataController::class, 'index']);
    Route::post('enterprise/{resource}', [EnterpriseDataController::class, 'store']);
    Route::put('enterprise/{resource}/{id}', [EnterpriseDataController::class, 'update']);
    Route::delete('enterprise/{resource}/{id}', [EnterpriseDataController::class, 'destroy']);
PHP;

$api = file_get_contents(backend('routes/api.php'));
$api = str_replace("    Route::apiResource('units', UnitController::class);\n", "    Route::apiResource('units', UnitController::class);\n{$routes}\n", $api);
put_file(backend('routes/api.php'), $api);

echo "Phase 2 backend files generated.\n";
