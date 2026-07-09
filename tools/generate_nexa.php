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

put_file(backend('bootstrap/app.php'), <<<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
PHP);

put_file(backend('app/Models/User.php'), <<<'PHP'
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'email',
        'password',
        'avatar_url',
        'phone',
        'locale',
        'theme',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
PHP);

put_file(backend('database/migrations/0001_01_01_000000_create_users_table.php'), <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar_url')->nullable();
            $table->string('phone')->nullable();
            $table->string('locale', 5)->default('en');
            $table->string('theme', 20)->default('light');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
PHP);

put_file(backend('database/migrations/2026_07_09_220000_create_nexa_core_tables.php'), <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('tax_number')->nullable();
            $table->string('logo_url')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('job_titles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_title_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('hire_date')->nullable();
            $table->decimal('salary', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->string('avatar_url')->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('group')->nullable();
            $table->string('status')->default('active');
            $table->string('source')->nullable();
            $table->string('contact_person')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('balance', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->nullable();
            $table->string('stage')->default('new');
            $table->decimal('estimated_value', 12, 2)->default(0);
            $table->date('follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('active');
            $table->decimal('balance', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('symbol');
            $table->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(10);
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->integer('quantity');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('movement_date')->useCurrent();
            $table->timestamps();
        });

        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->date('quote_date');
            $table->date('valid_until')->nullable();
            $table->string('status')->default('draft');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->date('order_date');
            $table->string('status')->default('confirmed');
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('status')->default('unpaid');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('paid_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('customer');
            $table->decimal('amount', 12, 2);
            $table->string('method')->default('bank');
            $table->date('payment_date');
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->date('order_date');
            $table->string('status')->default('draft');
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->date('invoice_date');
            $table->string('status')->default('unpaid');
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('paid_total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('payment_method')->default('bank');
            $table->timestamps();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('type');
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->boolean('is_cash_bank')->default(false);
            $table->timestamps();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->date('entry_date');
            $table->string('status')->default('posted');
            $table->text('memo')->nullable();
            $table->timestamps();
        });

        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('status')->default('present');
            $table->timestamps();
        });

        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('annual');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('pending');
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('period');
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2);
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('type')->default('info');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('group')->default('general');
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'group', 'key']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'audit_logs', 'settings', 'notifications', 'payrolls', 'leaves', 'attendance_records',
            'journal_entry_lines', 'journal_entries', 'accounts', 'expenses', 'purchase_invoices',
            'purchase_orders', 'payments', 'invoice_items', 'invoices', 'sales_orders', 'quotations',
            'stock_movements', 'products', 'warehouses', 'units', 'product_categories', 'suppliers',
            'leads', 'customers', 'employees', 'job_titles', 'departments', 'branches', 'companies',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
PHP);

$models = [
    'Company', 'Branch', 'Department', 'JobTitle', 'Employee', 'Customer', 'Lead', 'Supplier',
    'ProductCategory', 'Unit', 'Product', 'Warehouse', 'StockMovement', 'Quotation', 'SalesOrder',
    'Invoice', 'InvoiceItem', 'Payment', 'PurchaseOrder', 'PurchaseInvoice', 'Expense', 'Account',
    'JournalEntry', 'JournalEntryLine', 'AttendanceRecord', 'LeaveRequest', 'Payroll', 'Notification',
    'Setting', 'AuditLog',
];

foreach ($models as $model) {
    $table = match ($model) {
        'ProductCategory' => 'product_categories',
        'StockMovement' => 'stock_movements',
        'SalesOrder' => 'sales_orders',
        'InvoiceItem' => 'invoice_items',
        'PurchaseOrder' => 'purchase_orders',
        'PurchaseInvoice' => 'purchase_invoices',
        'JournalEntry' => 'journal_entries',
        'JournalEntryLine' => 'journal_entry_lines',
        'AttendanceRecord' => 'attendance_records',
        'LeaveRequest' => 'leaves',
        'AuditLog' => 'audit_logs',
        default => strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $model)).'s',
    };

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
        'settings' => 'array',
        'value' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'is_active' => 'boolean',
        'is_cash_bank' => 'boolean',
    ];
}
PHP);
}

put_file(backend('app/Http/Controllers/Api/GenericCrudController.php'), <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

abstract class GenericCrudController extends Controller
{
    protected string $modelClass;
    protected array $searchable = ['name', 'number', 'email', 'phone', 'sku', 'code', 'description'];
    protected array $validationRules = [];

    public function index(Request $request)
    {
        $model = new $this->modelClass();
        $query = $this->modelClass::query();

        $this->scopeCompany($query, $model, $request);

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->toString().'%';
            $query->where(function ($nested) use ($model, $term) {
                foreach ($this->searchable as $column) {
                    if (Schema::hasColumn($model->getTable(), $column)) {
                        $nested->orWhere($column, 'like', $term);
                    }
                }
            });
        }

        if ($request->filled('status') && Schema::hasColumn($model->getTable(), 'status')) {
            $query->where('status', $request->string('status'));
        }

        $sort = $request->string('sort', 'id')->toString();
        $direction = $request->string('direction', 'desc')->toString() === 'asc' ? 'asc' : 'desc';
        if (! Schema::hasColumn($model->getTable(), $sort)) {
            $sort = 'id';
        }

        return response()->json($query->orderBy($sort, $direction)->paginate((int) $request->integer('per_page', 10)));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $model = new $this->modelClass();

        if (Schema::hasColumn($model->getTable(), 'company_id') && empty($data['company_id'])) {
            $data['company_id'] = $request->user()?->company_id;
        }

        if (Schema::hasColumn($model->getTable(), 'branch_id') && empty($data['branch_id'])) {
            $data['branch_id'] = $request->user()?->branch_id;
        }

        $record = $this->modelClass::create($data);

        return response()->json($record, 201);
    }

    public function show(Request $request, int $id)
    {
        $model = new $this->modelClass();
        $query = $this->modelClass::query();
        $this->scopeCompany($query, $model, $request);

        return response()->json($query->findOrFail($id));
    }

    public function update(Request $request, int $id)
    {
        $record = $this->findScoped($request, $id);
        $record->update($this->validatedData($request, true));

        return response()->json($record->refresh());
    }

    public function destroy(Request $request, int $id)
    {
        $this->findScoped($request, $id)->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    protected function findScoped(Request $request, int $id): Model
    {
        $model = new $this->modelClass();
        $query = $this->modelClass::query();
        $this->scopeCompany($query, $model, $request);

        return $query->findOrFail($id);
    }

    protected function scopeCompany($query, Model $model, Request $request): void
    {
        $user = $request->user();
        if ($user && $user->company_id && Schema::hasColumn($model->getTable(), 'company_id')) {
            $query->where('company_id', $user->company_id);
        }
    }

    protected function validatedData(Request $request, bool $partial = false): array
    {
        if ($this->validationRules !== []) {
            return $request->validate($this->validationRules);
        }

        return collect($request->all())
            ->except(['id', 'created_at', 'updated_at'])
            ->filter(fn ($value) => $partial || $value !== null)
            ->toArray();
    }
}
PHP);

$controllerMap = [
    'Customer' => ['customers', ['name', 'email', 'phone', 'group', 'status', 'source']],
    'Lead' => ['leads', ['name', 'email', 'phone', 'stage', 'source']],
    'Supplier' => ['suppliers', ['name', 'email', 'phone', 'status']],
    'ProductCategory' => ['categories', ['name']],
    'Product' => ['products', ['name', 'sku', 'barcode']],
    'Warehouse' => ['warehouses', ['name', 'code', 'location']],
    'StockMovement' => ['stock-movements', ['reference', 'type', 'notes']],
    'Invoice' => ['invoices', ['number', 'status', 'notes']],
    'Payment' => ['payments', ['reference', 'method', 'type']],
    'PurchaseOrder' => ['purchase-orders', ['number', 'status']],
    'PurchaseInvoice' => ['purchase-invoices', ['number', 'status']],
    'Employee' => ['employees', ['employee_code', 'name', 'email', 'phone', 'status']],
    'AttendanceRecord' => ['attendance', ['status']],
    'LeaveRequest' => ['leaves', ['type', 'status', 'reason']],
    'Payroll' => ['payrolls', ['period', 'status']],
    'Account' => ['accounts', ['code', 'name', 'type']],
    'JournalEntry' => ['journal-entries', ['number', 'status', 'memo']],
    'Expense' => ['expenses', ['category', 'description', 'payment_method']],
    'Company' => ['companies', ['name', 'email', 'phone']],
    'Branch' => ['branches', ['name', 'code', 'city']],
    'Department' => ['departments', ['name']],
    'JobTitle' => ['job-titles', ['title']],
    'Quotation' => ['quotations', ['number', 'status']],
    'SalesOrder' => ['sales-orders', ['number', 'status']],
    'Unit' => ['units', ['name', 'symbol']],
];

foreach ($controllerMap as $model => [$route, $searchable]) {
    $controller = $model === 'LeaveRequest' ? 'LeaveController' : "{$model}Controller";
    $searchExport = var_export($searchable, true);
    put_file(backend("app/Http/Controllers/Api/{$controller}.php"), <<<PHP
<?php

namespace App\\Http\\Controllers\\Api;

use App\\Models\\{$model};

class {$controller} extends GenericCrudController
{
    protected string \$modelClass = {$model}::class;
    protected array \$searchable = {$searchExport};
}
PHP);
}

put_file(backend('app/Http/Controllers/Api/AuthController.php'), <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'company_name' => ['nullable', 'string', 'max:255'],
        ]);

        $company = Company::firstOrCreate(
            ['name' => $data['company_name'] ?? 'NexaERP Workspace'],
            ['email' => $data['email'], 'currency' => 'USD']
        );

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'company_id' => $company->id,
            'locale' => 'en',
        ]);
        $user->assignRole('Employee');

        return response()->json($this->tokenResponse($user), 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 422);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Your account is inactive.'], 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json($this->tokenResponse($user));
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load(['company', 'branch', 'roles', 'permissions']),
            'permissions' => $request->user()->getAllPermissions()->pluck('name')->values(),
            'roles' => $request->user()->roles->pluck('name')->values(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'avatar_url' => ['nullable', 'url', 'max:500'],
            'locale' => ['nullable', 'in:en,ar'],
            'theme' => ['nullable', 'in:light,dark'],
        ]);

        $request->user()->update($data);

        return response()->json($request->user()->fresh(['company', 'branch', 'roles']));
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($data['current_password'], $request->user()->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $request->user()->update(['password' => $data['password']]);
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Password changed. Please sign in again.']);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        return response()->json(['message' => 'Password reset delivery is configured for future mail integration.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        return response()->json(['message' => 'Reset token validation endpoint is ready for mail integration.']);
    }

    private function tokenResponse(User $user): array
    {
        return [
            'token' => $user->createToken('nexaerp-web')->plainTextToken,
            'user' => $user->load(['company', 'branch', 'roles']),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            'roles' => $user->roles->pluck('name')->values(),
        ];
    }
}
PHP);

put_file(backend('app/Http/Controllers/Api/DashboardController.php'), <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $companyId = $request->user()->company_id;
        $from = Carbon::parse($request->query('from', now()->startOfYear()->toDateString()));
        $to = Carbon::parse($request->query('to', now()->toDateString()));

        $revenue = Invoice::where('company_id', $companyId)->whereBetween('invoice_date', [$from, $to])->sum('grand_total');
        $expenses = Expense::where('company_id', $companyId)->whereBetween('expense_date', [$from, $to])->sum('amount');
        $paid = Invoice::where('company_id', $companyId)->where('status', 'paid')->sum('grand_total');

        $series = collect(range(5, 0))->map(function ($offset) use ($companyId) {
            $month = now()->subMonths($offset);
            return [
                'month' => $month->format('M'),
                'revenue' => (float) Invoice::where('company_id', $companyId)->whereYear('invoice_date', $month->year)->whereMonth('invoice_date', $month->month)->sum('grand_total'),
                'expenses' => (float) Expense::where('company_id', $companyId)->whereYear('expense_date', $month->year)->whereMonth('expense_date', $month->month)->sum('amount'),
            ];
        })->map(fn ($row) => $row + ['profit' => $row['revenue'] - $row['expenses']])->values();

        $lowStock = Product::where('company_id', $companyId)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->orderBy('stock_quantity')->limit(8)->get();

        return response()->json([
            'kpis' => [
                ['label' => 'Revenue', 'value' => round((float) $revenue, 2), 'change' => 12.4],
                ['label' => 'Expenses', 'value' => round((float) $expenses, 2), 'change' => -3.1],
                ['label' => 'Profit', 'value' => round((float) ($revenue - $expenses), 2), 'change' => 8.7],
                ['label' => 'Collections', 'value' => round((float) $paid, 2), 'change' => 5.2],
            ],
            'series' => $series,
            'counts' => [
                'customers' => Customer::where('company_id', $companyId)->count(),
                'suppliers' => Supplier::where('company_id', $companyId)->count(),
                'products' => Product::where('company_id', $companyId)->count(),
                'employees' => Employee::where('company_id', $companyId)->count(),
            ],
            'low_stock' => $lowStock,
            'recent_invoices' => Invoice::where('company_id', $companyId)->latest()->limit(6)->get(),
            'recent_customers' => Customer::where('company_id', $companyId)->latest()->limit(6)->get(),
            'employee_summary' => Employee::where('company_id', $companyId)->selectRaw('status, count(*) as total')->groupBy('status')->get(),
            'notifications' => [
                ['title' => 'Low stock review', 'body' => $lowStock->count().' products need attention.', 'type' => 'warning'],
                ['title' => 'Cashflow outlook', 'body' => 'Profit is '.number_format(max($revenue - $expenses, 0), 2).' for the selected period.', 'type' => 'info'],
            ],
            'quick_actions' => ['Create invoice', 'Add customer', 'Receive payment', 'Stock adjustment'],
        ]);
    }
}
PHP);

put_file(backend('app/Http/Controllers/Api/ReportController.php'), <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $companyId = $request->user()->company_id;

        return response()->json([
            'total_sales' => Invoice::where('company_id', $companyId)->sum('grand_total'),
            'paid_sales' => Invoice::where('company_id', $companyId)->where('status', 'paid')->sum('grand_total'),
            'overdue_sales' => Invoice::where('company_id', $companyId)->where('status', 'overdue')->sum('grand_total'),
            'rows' => Invoice::where('company_id', $companyId)->latest()->limit(30)->get(),
        ]);
    }

    public function purchases(Request $request)
    {
        $companyId = $request->user()->company_id;

        return response()->json([
            'total_purchases' => PurchaseOrder::where('company_id', $companyId)->sum('grand_total'),
            'rows' => PurchaseOrder::where('company_id', $companyId)->latest()->limit(30)->get(),
        ]);
    }

    public function inventory(Request $request)
    {
        $companyId = $request->user()->company_id;
        $products = Product::where('company_id', $companyId)->get();

        return response()->json([
            'valuation' => $products->sum(fn ($product) => $product->stock_quantity * $product->cost_price),
            'low_stock_count' => $products->filter(fn ($product) => $product->stock_quantity <= $product->low_stock_threshold)->count(),
            'rows' => $products->values(),
        ]);
    }

    public function profitLoss(Request $request)
    {
        $companyId = $request->user()->company_id;
        $income = Invoice::where('company_id', $companyId)->sum('grand_total');
        $expenses = Expense::where('company_id', $companyId)->sum('amount');

        return response()->json([
            'income' => round((float) $income, 2),
            'expenses' => round((float) $expenses, 2),
            'net_profit' => round((float) ($income - $expenses), 2),
        ]);
    }
}
PHP);

put_file(backend('app/Http/Controllers/Api/AiInsightController.php'), <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;

class AiInsightController extends Controller
{
    public function insights(Request $request)
    {
        $companyId = $request->user()->company_id;
        $revenue = (float) Invoice::where('company_id', $companyId)->sum('grand_total');
        $expenses = (float) Expense::where('company_id', $companyId)->sum('amount');
        $lowStock = Product::where('company_id', $companyId)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
        $customers = Customer::where('company_id', $companyId)->count();

        return response()->json([
            'provider' => env('OPENAI_API_KEY') ? env('AI_PROVIDER', 'openai') : 'mock',
            'summary' => 'Revenue is '.number_format($revenue, 2).' against expenses of '.number_format($expenses, 2).'. The current demo data suggests a '.($revenue >= $expenses ? 'profitable' : 'loss-making').' operating window.',
            'sales_trend' => $revenue > 50000 ? 'Sales trend is healthy. Prioritize collections and renewals.' : 'Sales volume is early-stage. Add campaigns for high-value leads.',
            'inventory_risk' => $lowStock > 0 ? "{$lowStock} products are below threshold. Reorder before invoice fulfillment is affected." : 'Inventory risk is low across tracked products.',
            'customer_segments' => $customers > 10 ? 'Segment customers into strategic, repeat, and nurture groups based on invoice value.' : 'Keep a high-touch onboarding segment until customer volume grows.',
            'expense_anomalies' => $expenses > ($revenue * 0.65) ? 'Expenses are elevated versus revenue. Review payroll, rent, and marketing categories.' : 'No major expense anomaly detected in current data.',
            'actions' => [
                'Review low-stock SKUs and create purchase orders.',
                'Contact customers with unpaid invoices older than 14 days.',
                'Compare branch-level margins before next hiring decision.',
            ],
        ]);
    }

    public function analyze(Request $request)
    {
        $request->validate(['prompt' => ['nullable', 'string', 'max:2000']]);

        return response()->json([
            'answer' => 'AI assistant is ready. Add OPENAI_API_KEY to enable live provider calls; meanwhile NexaERP returns grounded mock analysis from your ERP data.',
        ]);
    }
}
PHP);

put_file(backend('app/Http/Controllers/Api/SettingsController.php'), <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Setting::where('company_id', $request->user()->company_id)->get()->groupBy('group'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'group' => ['required', 'string', 'max:100'],
            'key' => ['required', 'string', 'max:100'],
            'value' => ['nullable'],
        ]);

        $setting = Setting::updateOrCreate(
            ['company_id' => $request->user()->company_id, 'group' => $data['group'], 'key' => $data['key']],
            ['value' => $data['value'] ?? null]
        );

        return response()->json($setting);
    }
}
PHP);

$routeLines = [];
foreach ($controllerMap as $model => [$route, $searchable]) {
    $controller = $model === 'LeaveRequest' ? 'LeaveController' : "{$model}Controller";
    $routeLines[] = "    Route::apiResource('{$route}', {$controller}::class);";
}

$routeUses = array_map(function ($model) {
    $controller = $model === 'LeaveRequest' ? 'LeaveController' : "{$model}Controller";
    return "use App\\Http\\Controllers\\Api\\{$controller};";
}, array_keys($controllerMap));

put_file(backend('routes/api.php'), "<?php\n\n"
    ."use App\\Http\\Controllers\\Api\\AiInsightController;\n"
    ."use App\\Http\\Controllers\\Api\\AuthController;\n"
    ."use App\\Http\\Controllers\\Api\\DashboardController;\n"
    ."use App\\Http\\Controllers\\Api\\ReportController;\n"
    ."use App\\Http\\Controllers\\Api\\SettingsController;\n"
    .implode("\n", $routeUses)."\n"
    ."use Illuminate\\Support\\Facades\\Route;\n\n"
    ."Route::prefix('auth')->group(function () {\n"
    ."    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');\n"
    ."    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:6,1');\n"
    ."    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);\n"
    ."    Route::post('reset-password', [AuthController::class, 'resetPassword']);\n"
    ."});\n\n"
    ."Route::middleware('auth:sanctum')->group(function () {\n"
    ."    Route::get('auth/me', [AuthController::class, 'me']);\n"
    ."    Route::put('auth/profile', [AuthController::class, 'updateProfile']);\n"
    ."    Route::put('auth/change-password', [AuthController::class, 'changePassword']);\n"
    ."    Route::post('auth/logout', [AuthController::class, 'logout']);\n"
    ."    Route::get('dashboard/summary', [DashboardController::class, 'summary']);\n"
    ."    Route::get('reports/sales', [ReportController::class, 'sales']);\n"
    ."    Route::get('reports/purchases', [ReportController::class, 'purchases']);\n"
    ."    Route::get('reports/inventory', [ReportController::class, 'inventory']);\n"
    ."    Route::get('reports/profit-loss', [ReportController::class, 'profitLoss']);\n"
    ."    Route::get('ai/insights', [AiInsightController::class, 'insights']);\n"
    ."    Route::post('ai/analyze', [AiInsightController::class, 'analyze']);\n"
    ."    Route::get('settings', [SettingsController::class, 'index']);\n"
    ."    Route::put('settings', [SettingsController::class, 'update']);\n"
    .implode("\n", $routeLines)."\n"
    ."});\n");

put_file(backend('database/seeders/DatabaseSeeder.php'), <<<'PHP'
<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JobTitle;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.view', 'users.create', 'users.update', 'users.delete',
            'customers.view', 'customers.create', 'customers.update', 'customers.delete',
            'sales.view', 'sales.create', 'sales.update', 'sales.delete',
            'purchases.view', 'purchases.create', 'purchases.update', 'purchases.delete',
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete',
            'hr.view', 'hr.create', 'hr.update', 'hr.delete',
            'accounting.view', 'accounting.create', 'accounting.update', 'accounting.delete',
            'reports.view', 'settings.update',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = ['Super Admin', 'Admin', 'Manager', 'Accountant', 'HR Manager', 'Sales Manager', 'Inventory Manager', 'Employee', 'Viewer'];
        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions(match ($roleName) {
                'Super Admin', 'Admin' => $permissions,
                'Accountant' => ['accounting.view', 'accounting.create', 'accounting.update', 'reports.view'],
                'HR Manager' => ['hr.view', 'hr.create', 'hr.update', 'reports.view'],
                'Sales Manager' => ['customers.view', 'customers.create', 'customers.update', 'sales.view', 'sales.create', 'sales.update', 'reports.view'],
                'Inventory Manager' => ['inventory.view', 'inventory.create', 'inventory.update', 'purchases.view', 'reports.view'],
                'Manager' => ['customers.view', 'sales.view', 'purchases.view', 'inventory.view', 'hr.view', 'accounting.view', 'reports.view'],
                'Employee' => ['customers.view', 'sales.view', 'inventory.view'],
                default => ['customers.view', 'sales.view', 'inventory.view', 'reports.view'],
            });
        }

        $company = Company::firstOrCreate(['name' => 'NexaERP Demo Company'], [
            'legal_name' => 'NexaERP Demo Company LLC',
            'email' => 'hello@nexaerp.com',
            'phone' => '+1 555 0100',
            'currency' => 'USD',
            'tax_number' => 'TAX-2026-NEXA',
            'settings' => ['language' => 'en', 'theme' => 'light', 'invoice_prefix' => 'INV'],
        ]);

        $main = Branch::firstOrCreate(['company_id' => $company->id, 'code' => 'HQ'], ['name' => 'Headquarters', 'city' => 'New York', 'country' => 'USA']);
        $branch = Branch::firstOrCreate(['company_id' => $company->id, 'code' => 'DXB'], ['name' => 'Regional Branch', 'city' => 'Dubai', 'country' => 'UAE']);

        foreach ($roles as $index => $roleName) {
            $email = $roleName === 'Super Admin' ? 'admin@nexaerp.com' : strtolower(str_replace(' ', '.', $roleName)).'@nexaerp.com';
            $user = User::updateOrCreate(['email' => $email], [
                'company_id' => $company->id,
                'branch_id' => $index % 2 === 0 ? $main->id : $branch->id,
                'name' => $roleName,
                'password' => Hash::make('password'),
                'locale' => 'en',
                'theme' => 'light',
                'is_active' => true,
            ]);
            $user->syncRoles([$roleName]);
        }

        $departments = collect(['Finance', 'Human Resources', 'Sales', 'Operations'])->map(fn ($name) => Department::firstOrCreate(['company_id' => $company->id, 'name' => $name]));
        $titles = collect(['Accountant', 'HR Specialist', 'Sales Executive', 'Warehouse Lead'])->map(fn ($title) => JobTitle::firstOrCreate(['company_id' => $company->id, 'title' => $title]));

        for ($i = 1; $i <= 16; $i++) {
            Employee::firstOrCreate(['employee_code' => sprintf('EMP-%03d', $i)], [
                'company_id' => $company->id,
                'branch_id' => $i % 2 ? $main->id : $branch->id,
                'department_id' => $departments[$i % $departments->count()]->id,
                'job_title_id' => $titles[$i % $titles->count()]->id,
                'name' => "Employee {$i}",
                'email' => "employee{$i}@nexaerp.com",
                'phone' => '+1 555 02'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'hire_date' => now()->subDays($i * 40)->toDateString(),
                'salary' => 3200 + ($i * 150),
                'status' => $i % 9 === 0 ? 'on_leave' : 'active',
            ]);
        }

        $categories = collect(['Hardware', 'Software', 'Services', 'Office'])->map(fn ($name) => ProductCategory::firstOrCreate(['company_id' => $company->id, 'name' => $name]));
        $unit = Unit::firstOrCreate(['company_id' => $company->id, 'symbol' => 'pcs'], ['name' => 'Pieces']);
        $warehouses = collect(['Central Warehouse', 'East Fulfillment', 'Branch Stock'])->map(fn ($name, $i) => Warehouse::firstOrCreate(['company_id' => $company->id, 'name' => $name], ['branch_id' => $i === 2 ? $branch->id : $main->id, 'code' => 'WH'.($i + 1), 'location' => $i === 2 ? 'Dubai' : 'New York']));

        for ($i = 1; $i <= 30; $i++) {
            Product::firstOrCreate(['sku' => sprintf('SKU-%04d', $i)], [
                'company_id' => $company->id,
                'product_category_id' => $categories[$i % $categories->count()]->id,
                'unit_id' => $unit->id,
                'name' => "Nexa Product {$i}",
                'barcode' => '6281000'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'cost_price' => 20 + ($i * 3),
                'sale_price' => 35 + ($i * 5),
                'stock_quantity' => $i % 7 === 0 ? 5 : 30 + $i,
                'low_stock_threshold' => 10,
                'is_active' => true,
            ]);
        }

        for ($i = 1; $i <= 20; $i++) {
            Customer::firstOrCreate(['company_id' => $company->id, 'email' => "customer{$i}@example.com"], [
                'branch_id' => $i % 2 ? $main->id : $branch->id,
                'name' => "Customer {$i}",
                'phone' => '+1 555 10'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'group' => $i % 3 === 0 ? 'Enterprise' : 'SMB',
                'status' => $i % 5 === 0 ? 'prospect' : 'active',
                'source' => ['Referral', 'Website', 'Campaign'][$i % 3],
                'contact_person' => "Contact {$i}",
                'follow_up_date' => now()->addDays($i)->toDateString(),
                'notes' => 'Demo customer profile with follow-up history.',
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            Supplier::firstOrCreate(['company_id' => $company->id, 'email' => "supplier{$i}@example.com"], [
                'name' => "Supplier {$i}",
                'phone' => '+1 555 20'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'status' => 'active',
            ]);
        }

        $customers = Customer::where('company_id', $company->id)->get();
        $products = Product::where('company_id', $company->id)->get();
        for ($i = 1; $i <= 18; $i++) {
            $subtotal = 700 + ($i * 85);
            $tax = round($subtotal * 0.14, 2);
            $invoice = Invoice::firstOrCreate(['number' => sprintf('INV-%05d', $i)], [
                'company_id' => $company->id,
                'branch_id' => $i % 2 ? $main->id : $branch->id,
                'customer_id' => $customers[$i % $customers->count()]->id,
                'invoice_date' => now()->subDays($i * 4)->toDateString(),
                'due_date' => now()->addDays(30 - $i)->toDateString(),
                'status' => ['paid', 'unpaid', 'partially_paid', 'overdue'][$i % 4],
                'subtotal' => $subtotal,
                'tax_total' => $tax,
                'discount_total' => 25,
                'paid_total' => $i % 4 === 0 ? $subtotal + $tax - 25 : 0,
                'grand_total' => $subtotal + $tax - 25,
                'notes' => 'PDF-ready invoice layout data.',
            ]);
            InvoiceItem::firstOrCreate(['invoice_id' => $invoice->id, 'description' => 'Implementation package'], [
                'product_id' => $products[$i % $products->count()]->id,
                'quantity' => 2,
                'unit_price' => $subtotal / 2,
                'tax_rate' => 14,
                'discount_rate' => 2,
                'line_total' => $subtotal + $tax - 25,
            ]);
            Payment::firstOrCreate(['company_id' => $company->id, 'reference' => sprintf('PAY-%05d', $i)], [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'type' => 'customer',
                'amount' => $invoice->paid_total ?: round($invoice->grand_total * 0.45, 2),
                'method' => ['bank', 'card', 'cash'][$i % 3],
                'payment_date' => now()->subDays($i)->toDateString(),
            ]);
        }

        $suppliers = Supplier::where('company_id', $company->id)->get();
        for ($i = 1; $i <= 10; $i++) {
            PurchaseOrder::firstOrCreate(['number' => sprintf('PO-%05d', $i)], [
                'company_id' => $company->id,
                'branch_id' => $i % 2 ? $main->id : $branch->id,
                'supplier_id' => $suppliers[$i % $suppliers->count()]->id,
                'order_date' => now()->subDays($i * 5)->toDateString(),
                'status' => ['draft', 'ordered', 'received'][$i % 3],
                'grand_total' => 1200 + ($i * 210),
            ]);
            PurchaseInvoice::firstOrCreate(['number' => sprintf('PI-%05d', $i)], [
                'company_id' => $company->id,
                'supplier_id' => $suppliers[$i % $suppliers->count()]->id,
                'invoice_date' => now()->subDays($i * 4)->toDateString(),
                'status' => $i % 2 ? 'paid' : 'unpaid',
                'grand_total' => 900 + ($i * 180),
                'paid_total' => $i % 2 ? 900 + ($i * 180) : 0,
            ]);
        }

        foreach (['Cash' => 'asset', 'Bank' => 'asset', 'Accounts Receivable' => 'asset', 'Revenue' => 'income', 'Cost of Goods Sold' => 'expense', 'Payroll Expense' => 'expense'] as $name => $type) {
            Account::firstOrCreate(['company_id' => $company->id, 'name' => $name], ['code' => strtoupper(substr($type, 0, 1)).rand(1000, 9999), 'type' => $type, 'is_cash_bank' => in_array($name, ['Cash', 'Bank'], true)]);
        }

        foreach (['Payroll', 'Rent', 'Marketing', 'Software', 'Logistics', 'Utilities'] as $i => $category) {
            Expense::firstOrCreate(['company_id' => $company->id, 'description' => "{$category} expense"], [
                'branch_id' => $i % 2 ? $main->id : $branch->id,
                'category' => $category,
                'amount' => 500 + ($i * 225),
                'expense_date' => now()->subDays($i * 8)->toDateString(),
                'payment_method' => 'bank',
            ]);
        }

        foreach ([
            ['company', 'currency', 'USD'],
            ['company', 'language', 'en'],
            ['company', 'theme', 'light'],
            ['tax', 'default_rate', 14],
            ['invoice', 'prefix', 'INV'],
        ] as [$group, $key, $value]) {
            Setting::updateOrCreate(['company_id' => $company->id, 'group' => $group, 'key' => $key], ['value' => $value]);
        }
    }
}
PHP);

put_file(backend('.env.example'), <<<'ENV'
APP_NAME=NexaERP
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexaerp
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173
FRONTEND_URL=http://localhost:5173

MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@nexaerp.com"
MAIL_FROM_NAME="${APP_NAME}"

OPENAI_API_KEY=
AI_PROVIDER=openai
ENV);

put_file(backend('.env'), str_replace('DB_CONNECTION=mysql', 'DB_CONNECTION=sqlite', file_get_contents(backend('.env.example'))));

put_file(frontend('package.json'), <<<'JSON'
{
  "name": "nexaerp-frontend",
  "private": true,
  "version": "1.0.0",
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "tsc -b && vite build",
    "preview": "vite preview"
  },
  "dependencies": {
    "@tailwindcss/vite": "^4.1.18",
    "axios": "^1.13.2",
    "lucide-react": "^0.562.0",
    "react": "^19.2.7",
    "react-dom": "^19.2.7",
    "react-router-dom": "^7.10.1",
    "recharts": "^3.6.0",
    "tailwindcss": "^4.1.18"
  },
  "devDependencies": {
    "@types/node": "^24.13.2",
    "@types/react": "^19.2.17",
    "@types/react-dom": "^19.2.3",
    "@vitejs/plugin-react": "^6.0.3",
    "typescript": "~6.0.2",
    "vite": "^8.1.1"
  }
}
JSON);

put_file(frontend('.env.example'), <<<'ENV'
VITE_API_BASE_URL=http://localhost:8000/api
VITE_APP_NAME=NexaERP
ENV);

put_file(frontend('vite.config.ts'), <<<'TS'
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [react(), tailwindcss()],
})
TS);

put_file(frontend('src/main.tsx'), <<<'TSX'
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import './index.css'
import App from './App'
import { AuthProvider } from './contexts/AuthContext'
import { ThemeProvider } from './contexts/ThemeContext'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <BrowserRouter>
      <ThemeProvider>
        <AuthProvider>
          <App />
        </AuthProvider>
      </ThemeProvider>
    </BrowserRouter>
  </StrictMode>,
)
TSX);

put_file(frontend('src/lib/modules.ts'), <<<'TS'
import {
  BadgeDollarSign,
  Boxes,
  Building2,
  ChartNoAxesCombined,
  CircleDollarSign,
  Contact,
  Factory,
  FileText,
  HandCoins,
  Landmark,
  Package,
  Receipt,
  Settings,
  Sparkles,
  Truck,
  UserRoundCog,
  Users,
} from 'lucide-react'

export type Field = { name: string; label: string; type?: 'text' | 'number' | 'date' | 'email' }
export type ModuleConfig = {
  key: string
  label: string
  endpoint: string
  permission: string
  fields: Field[]
  columns: string[]
  icon: typeof Users
}

export const modules: ModuleConfig[] = [
  { key: 'customers', label: 'Customers', endpoint: '/customers', permission: 'customers.view', icon: Users, columns: ['name', 'email', 'phone', 'group', 'status', 'balance'], fields: [{ name: 'name', label: 'Name' }, { name: 'email', label: 'Email', type: 'email' }, { name: 'phone', label: 'Phone' }, { name: 'group', label: 'Group' }, { name: 'status', label: 'Status' }, { name: 'source', label: 'Source' }] },
  { key: 'leads', label: 'Leads', endpoint: '/leads', permission: 'customers.view', icon: Contact, columns: ['name', 'email', 'phone', 'stage', 'estimated_value'], fields: [{ name: 'name', label: 'Name' }, { name: 'email', label: 'Email', type: 'email' }, { name: 'phone', label: 'Phone' }, { name: 'source', label: 'Source' }, { name: 'stage', label: 'Stage' }, { name: 'estimated_value', label: 'Estimated value', type: 'number' }] },
  { key: 'suppliers', label: 'Suppliers', endpoint: '/suppliers', permission: 'purchases.view', icon: Truck, columns: ['name', 'email', 'phone', 'status', 'balance'], fields: [{ name: 'name', label: 'Name' }, { name: 'email', label: 'Email', type: 'email' }, { name: 'phone', label: 'Phone' }, { name: 'status', label: 'Status' }] },
  { key: 'products', label: 'Products', endpoint: '/products', permission: 'inventory.view', icon: Package, columns: ['name', 'sku', 'barcode', 'stock_quantity', 'cost_price', 'sale_price'], fields: [{ name: 'name', label: 'Name' }, { name: 'sku', label: 'SKU' }, { name: 'barcode', label: 'Barcode' }, { name: 'stock_quantity', label: 'Stock', type: 'number' }, { name: 'cost_price', label: 'Cost', type: 'number' }, { name: 'sale_price', label: 'Sale price', type: 'number' }] },
  { key: 'warehouses', label: 'Warehouses', endpoint: '/warehouses', permission: 'inventory.view', icon: Boxes, columns: ['name', 'code', 'location', 'is_active'], fields: [{ name: 'name', label: 'Name' }, { name: 'code', label: 'Code' }, { name: 'location', label: 'Location' }] },
  { key: 'stock-movements', label: 'Stock Movements', endpoint: '/stock-movements', permission: 'inventory.view', icon: Factory, columns: ['warehouse_id', 'product_id', 'type', 'quantity', 'reference'], fields: [{ name: 'warehouse_id', label: 'Warehouse ID', type: 'number' }, { name: 'product_id', label: 'Product ID', type: 'number' }, { name: 'type', label: 'Type' }, { name: 'quantity', label: 'Quantity', type: 'number' }, { name: 'reference', label: 'Reference' }] },
  { key: 'invoices', label: 'Invoices', endpoint: '/invoices', permission: 'sales.view', icon: FileText, columns: ['number', 'customer_id', 'invoice_date', 'status', 'grand_total', 'paid_total'], fields: [{ name: 'customer_id', label: 'Customer ID', type: 'number' }, { name: 'number', label: 'Number' }, { name: 'invoice_date', label: 'Invoice date', type: 'date' }, { name: 'due_date', label: 'Due date', type: 'date' }, { name: 'status', label: 'Status' }, { name: 'grand_total', label: 'Grand total', type: 'number' }] },
  { key: 'payments', label: 'Payments', endpoint: '/payments', permission: 'sales.view', icon: HandCoins, columns: ['type', 'amount', 'method', 'payment_date', 'reference'], fields: [{ name: 'type', label: 'Type' }, { name: 'amount', label: 'Amount', type: 'number' }, { name: 'method', label: 'Method' }, { name: 'payment_date', label: 'Payment date', type: 'date' }, { name: 'reference', label: 'Reference' }] },
  { key: 'purchase-orders', label: 'Purchase Orders', endpoint: '/purchase-orders', permission: 'purchases.view', icon: Receipt, columns: ['number', 'supplier_id', 'order_date', 'status', 'grand_total'], fields: [{ name: 'supplier_id', label: 'Supplier ID', type: 'number' }, { name: 'number', label: 'Number' }, { name: 'order_date', label: 'Order date', type: 'date' }, { name: 'status', label: 'Status' }, { name: 'grand_total', label: 'Grand total', type: 'number' }] },
  { key: 'employees', label: 'Employees', endpoint: '/employees', permission: 'hr.view', icon: UserRoundCog, columns: ['employee_code', 'name', 'email', 'phone', 'salary', 'status'], fields: [{ name: 'employee_code', label: 'Employee code' }, { name: 'name', label: 'Name' }, { name: 'email', label: 'Email', type: 'email' }, { name: 'phone', label: 'Phone' }, { name: 'salary', label: 'Salary', type: 'number' }, { name: 'status', label: 'Status' }] },
  { key: 'expenses', label: 'Expenses', endpoint: '/expenses', permission: 'accounting.view', icon: CircleDollarSign, columns: ['category', 'description', 'amount', 'expense_date', 'payment_method'], fields: [{ name: 'category', label: 'Category' }, { name: 'description', label: 'Description' }, { name: 'amount', label: 'Amount', type: 'number' }, { name: 'expense_date', label: 'Date', type: 'date' }, { name: 'payment_method', label: 'Payment method' }] },
  { key: 'accounts', label: 'Accounts', endpoint: '/accounts', permission: 'accounting.view', icon: Landmark, columns: ['code', 'name', 'type', 'opening_balance', 'is_cash_bank'], fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Name' }, { name: 'type', label: 'Type' }, { name: 'opening_balance', label: 'Opening balance', type: 'number' }] },
]

export const utilityLinks = [
  { to: '/app/reports', label: 'Reports', icon: ChartNoAxesCombined },
  { to: '/app/ai', label: 'AI Insights', icon: Sparkles },
  { to: '/app/settings', label: 'Settings', icon: Settings },
  { to: '/app/companies', label: 'Companies', icon: Building2 },
  { to: '/app/payrolls', label: 'Payrolls', icon: BadgeDollarSign },
]
TS);

put_file(frontend('src/services/api.ts'), <<<'TS'
import axios from 'axios'

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api',
  headers: { Accept: 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('nexa_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})
TS);

put_file(frontend('src/i18n/translations.ts'), <<<'TS'
export type Lang = 'en' | 'ar'

const dictionary = {
  en: {
    dashboard: 'Dashboard',
    search: 'Search',
    create: 'Create',
    save: 'Save',
    cancel: 'Cancel',
    logout: 'Logout',
  },
  ar: {
    dashboard: 'لوحة التحكم',
    search: 'بحث',
    create: 'إضافة',
    save: 'حفظ',
    cancel: 'إلغاء',
    logout: 'تسجيل الخروج',
  },
} satisfies Record<Lang, Record<string, string>>

export function t(lang: Lang, key: string) {
  return dictionary[lang][key] ?? key
}
TS);

put_file(frontend('src/contexts/ThemeContext.tsx'), <<<'TSX'
import { createContext, useContext, useEffect, useMemo, useState } from 'react'
import type { Lang } from '../i18n/translations'

type ThemeContextValue = {
  theme: 'light' | 'dark'
  lang: Lang
  setTheme: (theme: 'light' | 'dark') => void
  setLang: (lang: Lang) => void
}

const ThemeContext = createContext<ThemeContextValue | null>(null)

export function ThemeProvider({ children }: { children: React.ReactNode }) {
  const [theme, setThemeState] = useState<'light' | 'dark'>(() => (localStorage.getItem('nexa_theme') as 'light' | 'dark') || 'light')
  const [lang, setLangState] = useState<Lang>(() => (localStorage.getItem('nexa_lang') as Lang) || 'en')

  useEffect(() => {
    document.documentElement.classList.toggle('dark', theme === 'dark')
    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr'
    document.documentElement.lang = lang
    localStorage.setItem('nexa_theme', theme)
    localStorage.setItem('nexa_lang', lang)
  }, [theme, lang])

  const value = useMemo(() => ({
    theme,
    lang,
    setTheme: setThemeState,
    setLang: setLangState,
  }), [theme, lang])

  return <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>
}

export function useTheme() {
  const context = useContext(ThemeContext)
  if (!context) throw new Error('useTheme must be used within ThemeProvider')
  return context
}
TSX);

put_file(frontend('src/contexts/AuthContext.tsx'), <<<'TSX'
import { createContext, useContext, useEffect, useMemo, useState } from 'react'
import { api } from '../services/api'

export type User = {
  id: number
  name: string
  email: string
  avatar_url?: string
  company?: { name: string; currency: string }
  roles?: { name: string }[]
}

type AuthContextValue = {
  user: User | null
  permissions: string[]
  loading: boolean
  login: (email: string, password: string) => Promise<void>
  register: (payload: { name: string; email: string; password: string; password_confirmation: string; company_name?: string }) => Promise<void>
  logout: () => Promise<void>
  refresh: () => Promise<void>
}

const AuthContext = createContext<AuthContextValue | null>(null)

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [permissions, setPermissions] = useState<string[]>([])
  const [loading, setLoading] = useState(true)

  async function applySession(data: { token?: string; user: User; permissions?: string[] }) {
    if (data.token) localStorage.setItem('nexa_token', data.token)
    setUser(data.user)
    setPermissions(data.permissions ?? [])
  }

  async function refresh() {
    if (!localStorage.getItem('nexa_token')) {
      setLoading(false)
      return
    }
    try {
      const { data } = await api.get('/auth/me')
      await applySession(data)
    } catch {
      localStorage.removeItem('nexa_token')
      setUser(null)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    refresh()
  }, [])

  async function login(email: string, password: string) {
    const { data } = await api.post('/auth/login', { email, password })
    await applySession(data)
  }

  async function register(payload: { name: string; email: string; password: string; password_confirmation: string; company_name?: string }) {
    const { data } = await api.post('/auth/register', payload)
    await applySession(data)
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } finally {
      localStorage.removeItem('nexa_token')
      setUser(null)
      setPermissions([])
    }
  }

  const value = useMemo(() => ({ user, permissions, loading, login, register, logout, refresh }), [user, permissions, loading])
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (!context) throw new Error('useAuth must be used within AuthProvider')
  return context
}
TSX);

put_file(frontend('src/components/ui/StatCard.tsx'), <<<'TSX'
export function StatCard({ label, value, change }: { label: string; value: string | number; change?: number }) {
  return (
    <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
      <p className="text-sm text-slate-500 dark:text-slate-400">{label}</p>
      <div className="mt-3 flex items-end justify-between gap-3">
        <strong className="text-2xl font-semibold text-slate-950 dark:text-white">{typeof value === 'number' ? value.toLocaleString() : value}</strong>
        {change !== undefined && <span className={change >= 0 ? 'text-sm text-emerald-600' : 'text-sm text-rose-600'}>{change >= 0 ? '+' : ''}{change}%</span>}
      </div>
    </div>
  )
}
TSX);

put_file(frontend('src/components/layout/Shell.tsx'), <<<'TSX'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { Bell, LayoutDashboard, LogOut, Menu, Moon, Search, Sun } from 'lucide-react'
import { modules, utilityLinks } from '../../lib/modules'
import { useAuth } from '../../contexts/AuthContext'
import { useTheme } from '../../contexts/ThemeContext'
import { t } from '../../i18n/translations'

export function Shell() {
  const { user, logout } = useAuth()
  const { theme, setTheme, lang, setLang } = useTheme()
  const navigate = useNavigate()

  async function signOut() {
    await logout()
    navigate('/login')
  }

  const navClass = ({ isActive }: { isActive: boolean }) =>
    `flex items-center gap-3 rounded-md px-3 py-2 text-sm transition ${isActive ? 'bg-slate-950 text-white dark:bg-white dark:text-slate-950' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900'}`

  return (
    <div className="min-h-screen bg-slate-100 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
      <aside className="fixed inset-y-0 start-0 z-30 hidden w-72 border-e border-slate-200 bg-white px-4 py-5 dark:border-slate-800 dark:bg-slate-950 lg:block">
        <div className="flex items-center gap-3 px-2">
          <div className="grid size-10 place-items-center rounded-lg bg-cyan-500 text-lg font-black text-white">N</div>
          <div>
            <p className="font-semibold">NexaERP</p>
            <p className="text-xs text-slate-500">Enterprise command center</p>
          </div>
        </div>
        <nav className="mt-8 space-y-1">
          <NavLink className={navClass} to="/app/dashboard"><LayoutDashboard size={18} />{t(lang, 'dashboard')}</NavLink>
          {modules.map((item) => <NavLink key={item.key} className={navClass} to={`/app/${item.key}`}><item.icon size={18} />{item.label}</NavLink>)}
          {utilityLinks.map((item) => <NavLink key={item.to} className={navClass} to={item.to}><item.icon size={18} />{item.label}</NavLink>)}
        </nav>
      </aside>

      <div className="lg:ps-72">
        <header className="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90">
          <button className="grid size-10 place-items-center rounded-md border border-slate-200 dark:border-slate-800 lg:hidden"><Menu size={18} /></button>
          <div className="hidden min-w-0 flex-1 items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-500 dark:border-slate-800 dark:bg-slate-900 md:flex">
            <Search size={18} />
            <span className="text-sm">Search customers, invoices, products...</span>
          </div>
          <button onClick={() => setLang(lang === 'en' ? 'ar' : 'en')} className="rounded-md border border-slate-200 px-3 py-2 text-sm dark:border-slate-800">{lang.toUpperCase()}</button>
          <button onClick={() => setTheme(theme === 'light' ? 'dark' : 'light')} className="grid size-10 place-items-center rounded-md border border-slate-200 dark:border-slate-800">{theme === 'light' ? <Moon size={18} /> : <Sun size={18} />}</button>
          <button className="grid size-10 place-items-center rounded-md border border-slate-200 dark:border-slate-800"><Bell size={18} /></button>
          <NavLink to="/app/profile" className="hidden items-center gap-3 md:flex">
            <div className="grid size-9 place-items-center rounded-full bg-slate-900 text-sm font-semibold text-white dark:bg-white dark:text-slate-950">{user?.name?.[0] ?? 'A'}</div>
            <div className="text-sm">
              <p className="font-medium">{user?.name}</p>
              <p className="text-xs text-slate-500">{user?.company?.name}</p>
            </div>
          </NavLink>
          <button onClick={signOut} className="grid size-10 place-items-center rounded-md border border-slate-200 dark:border-slate-800" title={t(lang, 'logout')}><LogOut size={18} /></button>
        </header>
        <main className="p-4 md:p-6">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
TSX);

put_file(frontend('src/pages/auth/LoginPage.tsx'), <<<'TSX'
import { FormEvent, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Loader2 } from 'lucide-react'
import { useAuth } from '../../contexts/AuthContext'

export function LoginPage() {
  const { login } = useAuth()
  const navigate = useNavigate()
  const [email, setEmail] = useState('admin@nexaerp.com')
  const [password, setPassword] = useState('password')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  async function submit(event: FormEvent) {
    event.preventDefault()
    setError('')
    setLoading(true)
    try {
      await login(email, password)
      navigate('/app/dashboard')
    } catch (err: any) {
      setError(err.response?.data?.message ?? 'Unable to sign in.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <main className="grid min-h-screen bg-slate-950 text-white lg:grid-cols-[1.1fr_0.9fr]">
      <section className="relative hidden overflow-hidden p-10 lg:block">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(6,182,212,.35),transparent_34%),linear-gradient(135deg,#020617,#0f172a_50%,#134e4a)]" />
        <div className="relative flex h-full flex-col justify-between">
          <div className="flex items-center gap-3"><div className="grid size-11 place-items-center rounded-lg bg-cyan-400 font-black text-slate-950">N</div><span className="text-xl font-semibold">NexaERP</span></div>
          <div className="max-w-2xl">
            <p className="mb-4 text-sm uppercase tracking-[.25em] text-cyan-200">Enterprise ERP SaaS</p>
            <h1 className="text-5xl font-semibold leading-tight">Run finance, sales, inventory, HR, and AI insights from one cockpit.</h1>
          </div>
        </div>
      </section>
      <section className="flex items-center justify-center p-6">
        <form onSubmit={submit} className="w-full max-w-md rounded-lg border border-white/10 bg-white p-8 text-slate-950 shadow-2xl dark:bg-slate-900 dark:text-white">
          <h2 className="text-2xl font-semibold">Welcome back</h2>
          <p className="mt-2 text-sm text-slate-500">Demo login: admin@nexaerp.com / password</p>
          {error && <div className="mt-4 rounded-md bg-rose-50 px-3 py-2 text-sm text-rose-700">{error}</div>}
          <label className="mt-6 block text-sm font-medium">Email<input className="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={email} onChange={(e) => setEmail(e.target.value)} /></label>
          <label className="mt-4 block text-sm font-medium">Password<input type="password" className="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={password} onChange={(e) => setPassword(e.target.value)} /></label>
          <button disabled={loading} className="mt-6 flex w-full items-center justify-center gap-2 rounded-md bg-slate-950 px-4 py-3 font-medium text-white dark:bg-cyan-400 dark:text-slate-950">{loading && <Loader2 className="animate-spin" size={18} />}Sign in</button>
          <div className="mt-5 flex justify-between text-sm text-slate-500"><Link to="/forgot-password">Forgot password?</Link><Link to="/register">Create account</Link></div>
        </form>
      </section>
    </main>
  )
}
TSX);

put_file(frontend('src/pages/auth/RegisterPage.tsx'), <<<'TSX'
import { FormEvent, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../../contexts/AuthContext'

export function RegisterPage() {
  const { register } = useAuth()
  const navigate = useNavigate()
  const [form, setForm] = useState({ name: '', email: '', company_name: '', password: '', password_confirmation: '' })
  const [error, setError] = useState('')

  async function submit(event: FormEvent) {
    event.preventDefault()
    try {
      await register(form)
      navigate('/app/dashboard')
    } catch (err: any) {
      setError(err.response?.data?.message ?? 'Registration failed.')
    }
  }

  return (
    <main className="grid min-h-screen place-items-center bg-slate-100 p-6 dark:bg-slate-950">
      <form onSubmit={submit} className="w-full max-w-lg rounded-lg bg-white p-8 shadow-sm dark:bg-slate-900">
        <h1 className="text-2xl font-semibold">Create NexaERP workspace</h1>
        {error && <p className="mt-4 rounded-md bg-rose-50 p-3 text-sm text-rose-700">{error}</p>}
        {Object.entries({ name: 'Name', email: 'Email', company_name: 'Company', password: 'Password', password_confirmation: 'Confirm password' }).map(([key, label]) => (
          <label key={key} className="mt-4 block text-sm font-medium">{label}<input type={key.includes('password') ? 'password' : 'text'} className="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={(form as any)[key]} onChange={(e) => setForm({ ...form, [key]: e.target.value })} /></label>
        ))}
        <button className="mt-6 w-full rounded-md bg-slate-950 px-4 py-3 font-medium text-white dark:bg-cyan-400 dark:text-slate-950">Create account</button>
        <Link to="/login" className="mt-5 block text-center text-sm text-slate-500">Back to login</Link>
      </form>
    </main>
  )
}
TSX);

put_file(frontend('src/pages/auth/SimpleAuthPage.tsx'), <<<'TSX'
import { Link } from 'react-router-dom'

export function SimpleAuthPage({ title }: { title: string }) {
  return (
    <main className="grid min-h-screen place-items-center bg-slate-100 p-6 dark:bg-slate-950">
      <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-sm dark:bg-slate-900">
        <h1 className="text-2xl font-semibold">{title}</h1>
        <p className="mt-2 text-sm text-slate-500">The backend endpoint is present and ready for mail delivery integration.</p>
        <input className="mt-6 w-full rounded-md border border-slate-200 px-3 py-2 dark:border-slate-700 dark:bg-slate-950" placeholder="Email address" />
        <button className="mt-4 w-full rounded-md bg-slate-950 px-4 py-3 font-medium text-white dark:bg-cyan-400 dark:text-slate-950">Continue</button>
        <Link to="/login" className="mt-5 block text-center text-sm text-slate-500">Back to login</Link>
      </div>
    </main>
  )
}
TSX);

put_file(frontend('src/pages/dashboard/DashboardPage.tsx'), <<<'TSX'
import { useEffect, useState } from 'react'
import { Area, AreaChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts'
import { Sparkles } from 'lucide-react'
import { api } from '../../services/api'
import { StatCard } from '../../components/ui/StatCard'

export function DashboardPage() {
  const [data, setData] = useState<any>(null)

  useEffect(() => {
    api.get('/dashboard/summary').then((response) => setData(response.data))
  }, [])

  if (!data) return <div className="grid gap-4 md:grid-cols-4">{Array.from({ length: 8 }).map((_, i) => <div key={i} className="h-32 animate-pulse rounded-lg bg-white dark:bg-slate-900" />)}</div>

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div><p className="text-sm text-slate-500">Today</p><h1 className="text-3xl font-semibold">Executive Dashboard</h1></div>
        <div className="flex gap-2"><input type="date" className="rounded-md border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900" /><input type="date" className="rounded-md border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900" /></div>
      </div>
      <section className="grid gap-4 md:grid-cols-4">{data.kpis.map((kpi: any) => <StatCard key={kpi.label} {...kpi} />)}</section>
      <section className="grid gap-4 xl:grid-cols-[1.6fr_0.9fr]">
        <div className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
          <h2 className="font-semibold">Revenue, expenses, profit</h2>
          <div className="mt-4 h-80">
            <ResponsiveContainer width="100%" height="100%">
              <AreaChart data={data.series}>
                <defs><linearGradient id="revenue" x1="0" y1="0" x2="0" y2="1"><stop offset="5%" stopColor="#06b6d4" stopOpacity={0.4}/><stop offset="95%" stopColor="#06b6d4" stopOpacity={0}/></linearGradient></defs>
                <CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" />
                <XAxis dataKey="month" /><YAxis /><Tooltip />
                <Area dataKey="revenue" stroke="#06b6d4" fill="url(#revenue)" />
                <Area dataKey="expenses" stroke="#f43f5e" fill="transparent" />
                <Area dataKey="profit" stroke="#10b981" fill="transparent" />
              </AreaChart>
            </ResponsiveContainer>
          </div>
        </div>
        <div className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
          <div className="flex items-center gap-2"><Sparkles size={18} className="text-cyan-500" /><h2 className="font-semibold">AI insights</h2></div>
          <div className="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">{data.notifications.map((item: any) => <div key={item.title} className="rounded-md bg-slate-50 p-3 dark:bg-slate-900"><strong className="block text-slate-950 dark:text-white">{item.title}</strong>{item.body}</div>)}</div>
        </div>
      </section>
      <section className="grid gap-4 lg:grid-cols-3">
        <Panel title="Recent invoices" rows={data.recent_invoices} columns={['number', 'status', 'grand_total']} />
        <Panel title="Recent customers" rows={data.recent_customers} columns={['name', 'email', 'status']} />
        <Panel title="Inventory alerts" rows={data.low_stock} columns={['name', 'sku', 'stock_quantity']} />
      </section>
    </div>
  )
}

function Panel({ title, rows, columns }: { title: string; rows: any[]; columns: string[] }) {
  return (
    <div className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
      <h2 className="font-semibold">{title}</h2>
      <div className="mt-4 space-y-2">{rows.map((row) => <div key={row.id} className="grid grid-cols-3 gap-2 rounded-md bg-slate-50 p-3 text-sm dark:bg-slate-900">{columns.map((column) => <span key={column} className="truncate">{row[column]}</span>)}</div>)}</div>
    </div>
  )
}
TSX);

put_file(frontend('src/pages/ModulePage.tsx'), <<<'TSX'
import { FormEvent, useEffect, useMemo, useState } from 'react'
import { useParams } from 'react-router-dom'
import { Plus, Printer, Search, Trash2 } from 'lucide-react'
import { modules, type ModuleConfig } from '../lib/modules'
import { api } from '../services/api'

export function ModulePage() {
  const { moduleKey } = useParams()
  const config = useMemo(() => modules.find((item) => item.key === moduleKey) ?? modules[0], [moduleKey])
  const [rows, setRows] = useState<any[]>([])
  const [query, setQuery] = useState('')
  const [formOpen, setFormOpen] = useState(false)
  const [form, setForm] = useState<Record<string, any>>({})
  const [loading, setLoading] = useState(true)

  async function load() {
    setLoading(true)
    const { data } = await api.get(config.endpoint, { params: { q: query } })
    setRows(data.data ?? data)
    setLoading(false)
  }

  useEffect(() => { load() }, [config.key])

  async function save(event: FormEvent) {
    event.preventDefault()
    const payload = Object.fromEntries(Object.entries(form).filter(([, value]) => value !== ''))
    await api.post(config.endpoint, payload)
    setForm({})
    setFormOpen(false)
    await load()
  }

  async function remove(id: number) {
    if (!confirm('Delete this record?')) return
    await api.delete(`${config.endpoint}/${id}`)
    await load()
  }

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div><p className="text-sm text-slate-500">Module</p><h1 className="text-3xl font-semibold">{config.label}</h1></div>
        <div className="flex gap-2"><button onClick={() => window.print()} className="grid size-10 place-items-center rounded-md border border-slate-200 dark:border-slate-800"><Printer size={18} /></button><button onClick={() => setFormOpen(true)} className="flex items-center gap-2 rounded-md bg-slate-950 px-4 py-2 text-white dark:bg-cyan-400 dark:text-slate-950"><Plus size={18} />Create</button></div>
      </div>
      <div className="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div className="flex flex-wrap items-center gap-3 border-b border-slate-200 p-4 dark:border-slate-800">
          <div className="flex min-w-72 flex-1 items-center gap-2 rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800"><Search size={18} /><input value={query} onChange={(e) => setQuery(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && load()} placeholder={`Search ${config.label.toLowerCase()}`} className="w-full bg-transparent outline-none" /></div>
          <button onClick={load} className="rounded-md border border-slate-200 px-4 py-2 dark:border-slate-800">Filter</button>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead><tr className="border-b border-slate-200 text-left text-slate-500 dark:border-slate-800">{config.columns.map((column) => <th key={column} className="px-4 py-3 font-medium">{column.replaceAll('_', ' ')}</th>)}<th className="px-4 py-3" /></tr></thead>
            <tbody>
              {loading ? <tr><td className="px-4 py-8 text-slate-500" colSpan={config.columns.length + 1}>Loading...</td></tr> : rows.length === 0 ? <tr><td className="px-4 py-8 text-slate-500" colSpan={config.columns.length + 1}>No records found.</td></tr> : rows.map((row) => <tr key={row.id} className="border-b border-slate-100 dark:border-slate-900">{config.columns.map((column) => <td key={column} className="max-w-56 truncate px-4 py-3">{String(row[column] ?? '-')}</td>)}<td className="px-4 py-3 text-right"><button onClick={() => remove(row.id)} className="grid size-8 place-items-center rounded-md text-rose-600 hover:bg-rose-50"><Trash2 size={16} /></button></td></tr>)}
            </tbody>
          </table>
        </div>
      </div>
      {formOpen && <Drawer config={config} form={form} setForm={setForm} onClose={() => setFormOpen(false)} onSave={save} />}
    </div>
  )
}

function Drawer({ config, form, setForm, onClose, onSave }: { config: ModuleConfig; form: Record<string, any>; setForm: (form: Record<string, any>) => void; onClose: () => void; onSave: (event: FormEvent) => void }) {
  return (
    <div className="fixed inset-0 z-50 bg-slate-950/30">
      <form onSubmit={onSave} className="ms-auto h-full w-full max-w-md overflow-y-auto bg-white p-6 shadow-2xl dark:bg-slate-950">
        <div className="flex items-center justify-between"><h2 className="text-xl font-semibold">Create {config.label}</h2><button type="button" onClick={onClose} className="rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800">Cancel</button></div>
        <div className="mt-6 space-y-4">{config.fields.map((field) => <label key={field.name} className="block text-sm font-medium">{field.label}<input type={field.type ?? 'text'} value={form[field.name] ?? ''} onChange={(e) => setForm({ ...form, [field.name]: field.type === 'number' ? Number(e.target.value) : e.target.value })} className="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800 dark:bg-slate-900" /></label>)}</div>
        <button className="mt-6 w-full rounded-md bg-slate-950 px-4 py-3 font-medium text-white dark:bg-cyan-400 dark:text-slate-950">Save</button>
      </form>
    </div>
  )
}
TSX);

put_file(frontend('src/pages/ReportsPage.tsx'), <<<'TSX'
import { useEffect, useState } from 'react'
import { api } from '../services/api'
import { StatCard } from '../components/ui/StatCard'

export function ReportsPage() {
  const [data, setData] = useState<any>({})

  useEffect(() => {
    Promise.all([
      api.get('/reports/sales'),
      api.get('/reports/purchases'),
      api.get('/reports/inventory'),
      api.get('/reports/profit-loss'),
    ]).then(([sales, purchases, inventory, profit]) => setData({ sales: sales.data, purchases: purchases.data, inventory: inventory.data, profit: profit.data }))
  }, [])

  return (
    <div className="space-y-5">
      <h1 className="text-3xl font-semibold">Reports</h1>
      <div className="grid gap-4 md:grid-cols-4">
        <StatCard label="Sales" value={data.sales?.total_sales ?? 0} />
        <StatCard label="Purchases" value={data.purchases?.total_purchases ?? 0} />
        <StatCard label="Inventory valuation" value={data.inventory?.valuation ?? 0} />
        <StatCard label="Net profit" value={data.profit?.net_profit ?? 0} />
      </div>
      <div className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
        <h2 className="font-semibold">Export-ready analytics structure</h2>
        <pre className="mt-4 max-h-96 overflow-auto rounded-md bg-slate-950 p-4 text-xs text-slate-100">{JSON.stringify(data, null, 2)}</pre>
      </div>
    </div>
  )
}
TSX);

put_file(frontend('src/pages/AiPage.tsx'), <<<'TSX'
import { useEffect, useState } from 'react'
import { Send, Sparkles } from 'lucide-react'
import { api } from '../services/api'

export function AiPage() {
  const [insights, setInsights] = useState<any>(null)
  const [messages, setMessages] = useState<string[]>(['Ask about sales trends, stock risk, customer segments, or expense anomalies.'])

  useEffect(() => {
    api.get('/ai/insights').then((response) => setInsights(response.data))
  }, [])

  return (
    <div className="space-y-5">
      <div className="flex items-center gap-3"><Sparkles className="text-cyan-500" /><h1 className="text-3xl font-semibold">AI Insights</h1></div>
      {insights && <section className="grid gap-4 md:grid-cols-2">
        {['summary', 'sales_trend', 'inventory_risk', 'customer_segments', 'expense_anomalies'].map((key) => <div key={key} className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950"><p className="text-sm uppercase text-slate-500">{key.replaceAll('_', ' ')}</p><p className="mt-3 text-slate-700 dark:text-slate-200">{insights[key]}</p></div>)}
      </section>}
      <section className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
        <h2 className="font-semibold">Assistant chat</h2>
        <div className="mt-4 space-y-3">{messages.map((message, index) => <div key={index} className="rounded-md bg-slate-100 p-3 text-sm dark:bg-slate-900">{message}</div>)}</div>
        <form className="mt-4 flex gap-2" onSubmit={(event) => { event.preventDefault(); const input = event.currentTarget.elements.namedItem('prompt') as HTMLInputElement; setMessages([...messages, input.value || 'Show next best actions.', 'Mock AI: review low stock, overdue invoices, and expense ratio this week.']); input.value = '' }}>
          <input name="prompt" className="flex-1 rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800 dark:bg-slate-900" placeholder="Ask Nexa AI..." />
          <button className="grid size-10 place-items-center rounded-md bg-slate-950 text-white dark:bg-cyan-400 dark:text-slate-950"><Send size={18} /></button>
        </form>
      </section>
    </div>
  )
}
TSX);

put_file(frontend('src/pages/SettingsPage.tsx'), <<<'TSX'
import { FormEvent, useEffect, useState } from 'react'
import { api } from '../services/api'

export function SettingsPage() {
  const [settings, setSettings] = useState<any>({})
  const [form, setForm] = useState({ group: 'company', key: 'currency', value: 'USD' })

  async function load() {
    const { data } = await api.get('/settings')
    setSettings(data)
  }

  useEffect(() => { load() }, [])

  async function save(event: FormEvent) {
    event.preventDefault()
    await api.put('/settings', form)
    await load()
  }

  return (
    <div className="space-y-5">
      <h1 className="text-3xl font-semibold">Settings</h1>
      <form onSubmit={save} className="grid gap-3 rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950 md:grid-cols-4">
        {(['group', 'key', 'value'] as const).map((field) => <input key={field} value={form[field]} onChange={(e) => setForm({ ...form, [field]: e.target.value })} className="rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800 dark:bg-slate-900" placeholder={field} />)}
        <button className="rounded-md bg-slate-950 px-4 py-2 text-white dark:bg-cyan-400 dark:text-slate-950">Save</button>
      </form>
      <pre className="rounded-lg bg-slate-950 p-5 text-sm text-slate-100">{JSON.stringify(settings, null, 2)}</pre>
    </div>
  )
}
TSX);

put_file(frontend('src/pages/ProfilePage.tsx'), <<<'TSX'
import { FormEvent, useState } from 'react'
import { api } from '../services/api'
import { useAuth } from '../contexts/AuthContext'

export function ProfilePage() {
  const { user, refresh } = useAuth()
  const [form, setForm] = useState({ name: user?.name ?? '', phone: '', avatar_url: '', locale: 'en', theme: 'light' })

  async function save(event: FormEvent) {
    event.preventDefault()
    await api.put('/auth/profile', form)
    await refresh()
  }

  return (
    <div className="max-w-2xl space-y-5">
      <h1 className="text-3xl font-semibold">Profile</h1>
      <form onSubmit={save} className="space-y-4 rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
        {Object.keys(form).map((field) => <label key={field} className="block text-sm font-medium">{field.replaceAll('_', ' ')}<input value={(form as any)[field]} onChange={(e) => setForm({ ...form, [field]: e.target.value })} className="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800 dark:bg-slate-900" /></label>)}
        <button className="rounded-md bg-slate-950 px-4 py-2 text-white dark:bg-cyan-400 dark:text-slate-950">Update profile</button>
      </form>
    </div>
  )
}
TSX);

put_file(frontend('src/App.tsx'), <<<'TSX'
import { Navigate, Route, Routes } from 'react-router-dom'
import { Shell } from './components/layout/Shell'
import { useAuth } from './contexts/AuthContext'
import { LoginPage } from './pages/auth/LoginPage'
import { RegisterPage } from './pages/auth/RegisterPage'
import { SimpleAuthPage } from './pages/auth/SimpleAuthPage'
import { DashboardPage } from './pages/dashboard/DashboardPage'
import { ModulePage } from './pages/ModulePage'
import { ReportsPage } from './pages/ReportsPage'
import { AiPage } from './pages/AiPage'
import { SettingsPage } from './pages/SettingsPage'
import { ProfilePage } from './pages/ProfilePage'

function Protected({ children }: { children: React.ReactNode }) {
  const { user, loading } = useAuth()
  if (loading) return <div className="grid min-h-screen place-items-center bg-slate-100 dark:bg-slate-950">Loading NexaERP...</div>
  if (!user) return <Navigate to="/login" replace />
  return children
}

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Navigate to="/app/dashboard" replace />} />
      <Route path="/login" element={<LoginPage />} />
      <Route path="/register" element={<RegisterPage />} />
      <Route path="/forgot-password" element={<SimpleAuthPage title="Forgot password" />} />
      <Route path="/reset-password" element={<SimpleAuthPage title="Reset password" />} />
      <Route path="/app" element={<Protected><Shell /></Protected>}>
        <Route index element={<Navigate to="/app/dashboard" replace />} />
        <Route path="dashboard" element={<DashboardPage />} />
        <Route path="reports" element={<ReportsPage />} />
        <Route path="ai" element={<AiPage />} />
        <Route path="settings" element={<SettingsPage />} />
        <Route path="profile" element={<ProfilePage />} />
        <Route path=":moduleKey" element={<ModulePage />} />
      </Route>
    </Routes>
  )
}
TSX);

put_file(frontend('src/index.css'), <<<'CSS'
@import "tailwindcss";

@theme {
  --font-sans: Inter, ui-sans-serif, system-ui, sans-serif;
}

html {
  color-scheme: light;
}

html.dark {
  color-scheme: dark;
}

body {
  margin: 0;
  min-width: 320px;
  min-height: 100vh;
  font-family: Inter, ui-sans-serif, system-ui, sans-serif;
}

button,
input,
select,
textarea {
  font: inherit;
}

button {
  cursor: pointer;
}

@media print {
  aside,
  header,
  button {
    display: none !important;
  }

  main {
    padding: 0 !important;
  }
}
CSS);

put_file($root.'/README.md', <<<'MD'
# NexaERP

NexaERP is a portfolio-ready enterprise ERP MVP built with Laravel 12, Sanctum, Spatie Permission, React, TypeScript, Vite, Tailwind CSS, Recharts, Axios, and lucide-react.

## Features

- Sanctum API authentication: login, register, logout, profile update, change password, forgot/reset password endpoints.
- Role-based access foundation with Super Admin, Admin, Manager, Accountant, HR Manager, Sales Manager, Inventory Manager, Employee, and Viewer.
- Multi-company and branch-aware schema.
- ERP modules for CRM, sales, purchases, inventory, HR, accounting, reports, settings, and AI insights.
- REST API endpoints under `/api`.
- React dashboard with KPI cards, charts, low stock alerts, recent invoices/customers, AI panel, dark/light theme, and English/Arabic direction support.
- Demo seed data for one company, two branches, all roles, customers, suppliers, products, warehouses, invoices, payments, employees, expenses, accounts, and settings.

## Demo Account

- Email: `admin@nexaerp.com`
- Password: `password`

## Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

The default `.env.example` is MySQL-oriented. For quick local testing you can set `DB_CONNECTION=sqlite` and create `database/database.sqlite`.

## Frontend Setup

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

Set `VITE_API_BASE_URL=http://localhost:8000/api` when the Laravel server runs on port 8000.

## AI Configuration

No paid API key is committed. Configure these only in your local environment:

```env
OPENAI_API_KEY=
AI_PROVIDER=openai
```

Without a key, `/api/ai/insights` returns smart mock insights based on ERP database data.

## API Overview

Important endpoints include:

- `POST /api/auth/login`
- `POST /api/auth/register`
- `POST /api/auth/logout`
- `GET /api/auth/me`
- `GET /api/dashboard/summary`
- CRUD: `/api/customers`, `/api/leads`, `/api/suppliers`, `/api/products`, `/api/warehouses`, `/api/stock-movements`, `/api/invoices`, `/api/payments`, `/api/purchase-orders`, `/api/employees`, `/api/attendance`, `/api/payrolls`, `/api/accounts`, `/api/journal-entries`, `/api/expenses`
- Reports: `/api/reports/sales`, `/api/reports/purchases`, `/api/reports/inventory`, `/api/reports/profit-loss`
- AI: `/api/ai/insights`, `/api/ai/analyze`
- Settings: `/api/settings`

## Deployment Notes

- Use MySQL or PostgreSQL-compatible schema settings in production.
- Set real `APP_KEY`, mail credentials, queue worker, scheduler, cache, and HTTPS CORS/Sanctum domains.
- Keep API keys and credentials in environment variables only.
- Add module-specific policies and deeper form request validation before production SaaS launch.

## Future Improvements

- Dedicated quotation/order/invoice item builders.
- PDF rendering pipeline for invoices and purchase orders.
- Full audit log hooks per model.
- Live OpenAI provider integration behind the existing AI service boundary.
- Advanced exports for reports and accounting statements.
MD);

put_file($root.'/.gitignore', <<<'TXT'
/backend/vendor
/backend/.env
/backend/database/*.sqlite
/frontend/node_modules
/frontend/dist
/.npm-cache
TXT);

echo "NexaERP files generated.\n";
