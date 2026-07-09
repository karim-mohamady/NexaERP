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
            $email = match ($roleName) {
                'Super Admin' => 'admin@nexaerp.com',
                'Admin' => 'admin.user@nexaerp.com',
                default => strtolower(str_replace(' ', '.', $roleName)).'@nexaerp.com',
            };
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
