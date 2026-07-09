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
  ClipboardCheck,
  FileClock,
  Truck,
  UserRoundCog,
  Users,
} from 'lucide-react'

export type Field = { name: string; label: string; type?: 'text' | 'number' | 'date' | 'email' }
export type ModuleConfig = {
  key: string
  labelKey: string
  label: string
  endpoint: string
  permission: string
  fields: Field[]
  columns: string[]
  icon: typeof Users
}

export const modules: ModuleConfig[] = [
  { key: 'customers', labelKey: 'customers', label: 'Customers', endpoint: '/customers', permission: 'customers.view', icon: Users, columns: ['name', 'email', 'phone', 'group', 'status', 'balance'], fields: [{ name: 'name', label: 'Name' }, { name: 'email', label: 'Email', type: 'email' }, { name: 'phone', label: 'Phone' }, { name: 'group', label: 'Group' }, { name: 'status', label: 'Status' }, { name: 'source', label: 'Source' }] },
  { key: 'leads', labelKey: 'leads', label: 'Leads', endpoint: '/leads', permission: 'customers.view', icon: Contact, columns: ['name', 'email', 'phone', 'stage', 'estimated_value'], fields: [{ name: 'name', label: 'Name' }, { name: 'email', label: 'Email', type: 'email' }, { name: 'phone', label: 'Phone' }, { name: 'source', label: 'Source' }, { name: 'stage', label: 'Stage' }, { name: 'estimated_value', label: 'Estimated value', type: 'number' }] },
  { key: 'suppliers', labelKey: 'suppliers', label: 'Suppliers', endpoint: '/suppliers', permission: 'purchases.view', icon: Truck, columns: ['name', 'email', 'phone', 'status', 'balance'], fields: [{ name: 'name', label: 'Name' }, { name: 'email', label: 'Email', type: 'email' }, { name: 'phone', label: 'Phone' }, { name: 'status', label: 'Status' }] },
  { key: 'products', labelKey: 'products', label: 'Products', endpoint: '/products', permission: 'inventory.view', icon: Package, columns: ['name', 'sku', 'barcode', 'stock_quantity', 'cost_price', 'sale_price'], fields: [{ name: 'name', label: 'Name' }, { name: 'sku', label: 'SKU' }, { name: 'barcode', label: 'Barcode' }, { name: 'stock_quantity', label: 'Stock', type: 'number' }, { name: 'cost_price', label: 'Cost', type: 'number' }, { name: 'sale_price', label: 'Sale price', type: 'number' }] },
  { key: 'warehouses', labelKey: 'warehouses', label: 'Warehouses', endpoint: '/warehouses', permission: 'inventory.view', icon: Boxes, columns: ['name', 'code', 'location', 'is_active'], fields: [{ name: 'name', label: 'Name' }, { name: 'code', label: 'Code' }, { name: 'location', label: 'Location' }] },
  { key: 'stock-movements', labelKey: 'stockMovements', label: 'Stock Movements', endpoint: '/stock-movements', permission: 'inventory.view', icon: Factory, columns: ['warehouse_id', 'product_id', 'type', 'quantity', 'reference'], fields: [{ name: 'warehouse_id', label: 'Warehouse ID', type: 'number' }, { name: 'product_id', label: 'Product ID', type: 'number' }, { name: 'type', label: 'Type' }, { name: 'quantity', label: 'Quantity', type: 'number' }, { name: 'reference', label: 'Reference' }] },
  { key: 'invoices', labelKey: 'invoices', label: 'Invoices', endpoint: '/invoices', permission: 'sales.view', icon: FileText, columns: ['number', 'customer_id', 'invoice_date', 'status', 'grand_total', 'paid_total'], fields: [{ name: 'customer_id', label: 'Customer ID', type: 'number' }, { name: 'number', label: 'Number' }, { name: 'invoice_date', label: 'Invoice date', type: 'date' }, { name: 'due_date', label: 'Due date', type: 'date' }, { name: 'status', label: 'Status' }, { name: 'grand_total', label: 'Grand total', type: 'number' }] },
  { key: 'payments', labelKey: 'payments', label: 'Payments', endpoint: '/payments', permission: 'sales.view', icon: HandCoins, columns: ['type', 'amount', 'method', 'payment_date', 'reference'], fields: [{ name: 'type', label: 'Type' }, { name: 'amount', label: 'Amount', type: 'number' }, { name: 'method', label: 'Method' }, { name: 'payment_date', label: 'Payment date', type: 'date' }, { name: 'reference', label: 'Reference' }] },
  { key: 'purchase-orders', labelKey: 'purchaseOrders', label: 'Purchase Orders', endpoint: '/purchase-orders', permission: 'purchases.view', icon: Receipt, columns: ['number', 'supplier_id', 'order_date', 'status', 'grand_total'], fields: [{ name: 'supplier_id', label: 'Supplier ID', type: 'number' }, { name: 'number', label: 'Number' }, { name: 'order_date', label: 'Order date', type: 'date' }, { name: 'status', label: 'Status' }, { name: 'grand_total', label: 'Grand total', type: 'number' }] },
  { key: 'purchase-invoices', labelKey: 'purchaseInvoices', label: 'Purchase Invoices', endpoint: '/purchase-invoices', permission: 'purchases.view', icon: Receipt, columns: ['number', 'supplier_id', 'invoice_date', 'status', 'grand_total', 'paid_total'], fields: [{ name: 'supplier_id', label: 'Supplier ID', type: 'number' }, { name: 'number', label: 'Number' }, { name: 'invoice_date', label: 'Invoice date', type: 'date' }, { name: 'status', label: 'Status' }, { name: 'grand_total', label: 'Grand total', type: 'number' }] },
  { key: 'quotations', labelKey: 'quotations', label: 'Quotations', endpoint: '/quotations', permission: 'sales.view', icon: FileText, columns: ['number', 'customer_id', 'quote_date', 'valid_until', 'status', 'grand_total'], fields: [{ name: 'customer_id', label: 'Customer ID', type: 'number' }, { name: 'number', label: 'Number' }, { name: 'quote_date', label: 'Quote date', type: 'date' }, { name: 'valid_until', label: 'Valid until', type: 'date' }, { name: 'status', label: 'Status' }, { name: 'grand_total', label: 'Grand total', type: 'number' }] },
  { key: 'sales-orders', labelKey: 'salesOrders', label: 'Sales Orders', endpoint: '/sales-orders', permission: 'sales.view', icon: FileText, columns: ['number', 'customer_id', 'order_date', 'status', 'grand_total'], fields: [{ name: 'customer_id', label: 'Customer ID', type: 'number' }, { name: 'number', label: 'Number' }, { name: 'order_date', label: 'Order date', type: 'date' }, { name: 'status', label: 'Status' }, { name: 'grand_total', label: 'Grand total', type: 'number' }] },
  { key: 'employees', labelKey: 'employees', label: 'Employees', endpoint: '/employees', permission: 'hr.view', icon: UserRoundCog, columns: ['employee_code', 'name', 'email', 'phone', 'salary', 'status'], fields: [{ name: 'employee_code', label: 'Employee code' }, { name: 'name', label: 'Name' }, { name: 'email', label: 'Email', type: 'email' }, { name: 'phone', label: 'Phone' }, { name: 'salary', label: 'Salary', type: 'number' }, { name: 'status', label: 'Status' }] },
  { key: 'attendance', labelKey: 'attendance', label: 'Attendance', endpoint: '/attendance', permission: 'hr.view', icon: UserRoundCog, columns: ['employee_id', 'work_date', 'check_in', 'check_out', 'status'], fields: [{ name: 'employee_id', label: 'Employee ID', type: 'number' }, { name: 'work_date', label: 'Work date', type: 'date' }, { name: 'check_in', label: 'Check in' }, { name: 'check_out', label: 'Check out' }, { name: 'status', label: 'Status' }] },
  { key: 'payrolls', labelKey: 'payrolls', label: 'Payrolls', endpoint: '/payrolls', permission: 'hr.view', icon: BadgeDollarSign, columns: ['employee_id', 'period', 'basic_salary', 'allowances', 'deductions', 'net_salary', 'status'], fields: [{ name: 'employee_id', label: 'Employee ID', type: 'number' }, { name: 'period', label: 'Period' }, { name: 'basic_salary', label: 'Basic salary', type: 'number' }, { name: 'allowances', label: 'Allowances', type: 'number' }, { name: 'deductions', label: 'Deductions', type: 'number' }, { name: 'net_salary', label: 'Net salary', type: 'number' }, { name: 'status', label: 'Status' }] },
  { key: 'expenses', labelKey: 'expenses', label: 'Expenses', endpoint: '/expenses', permission: 'accounting.view', icon: CircleDollarSign, columns: ['category', 'description', 'amount', 'expense_date', 'payment_method'], fields: [{ name: 'category', label: 'Category' }, { name: 'description', label: 'Description' }, { name: 'amount', label: 'Amount', type: 'number' }, { name: 'expense_date', label: 'Date', type: 'date' }, { name: 'payment_method', label: 'Payment method' }] },
  { key: 'accounts', labelKey: 'accounts', label: 'Accounts', endpoint: '/accounts', permission: 'accounting.view', icon: Landmark, columns: ['code', 'name', 'type', 'opening_balance', 'is_cash_bank'], fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Name' }, { name: 'type', label: 'Type' }, { name: 'opening_balance', label: 'Opening balance', type: 'number' }] },
  { key: 'companies', labelKey: 'companies', label: 'Companies', endpoint: '/companies', permission: 'settings.update', icon: Building2, columns: ['name', 'legal_name', 'email', 'phone', 'currency'], fields: [{ name: 'name', label: 'Name' }, { name: 'legal_name', label: 'Legal name' }, { name: 'email', label: 'Email', type: 'email' }, { name: 'phone', label: 'Phone' }, { name: 'currency', label: 'Currency' }] },
  { key: 'branches', labelKey: 'branches', label: 'Branches', endpoint: '/branches', permission: 'settings.update', icon: Building2, columns: ['name', 'code', 'city', 'country', 'is_active'], fields: [{ name: 'name', label: 'Name' }, { name: 'code', label: 'Code' }, { name: 'city', label: 'City' }, { name: 'country', label: 'Country' }] },
  { key: 'departments', labelKey: 'departments', label: 'Departments', endpoint: '/departments', permission: 'hr.view', icon: UserRoundCog, columns: ['name', 'description'], fields: [{ name: 'name', label: 'Name' }, { name: 'description', label: 'Description' }] },
  { key: 'job-titles', labelKey: 'jobTitles', label: 'Job Titles', endpoint: '/job-titles', permission: 'hr.view', icon: UserRoundCog, columns: ['title'], fields: [{ name: 'title', label: 'Title' }] },
]

export const utilityLinks = [
  { to: '/app/reports', labelKey: 'reports', label: 'Reports', icon: ChartNoAxesCombined },
  { to: '/app/report-builder', labelKey: 'reportBuilder', label: 'Report Builder', icon: ChartNoAxesCombined },
  { to: '/app/dashboard-builder', labelKey: 'dashboardBuilder', label: 'Dashboard Builder', icon: ChartNoAxesCombined },
  { to: '/app/workflows', labelKey: 'workflows', label: 'Workflows', icon: ClipboardCheck },
  { to: '/app/approvals', labelKey: 'approvals', label: 'Approvals', icon: ClipboardCheck },
  { to: '/app/my-requests', labelKey: 'myRequests', label: 'My Requests', icon: FileClock },
  { to: '/app/audit-logs', labelKey: 'auditLogs', label: 'Audit Logs', icon: FileClock },
  { to: '/app/notifications', labelKey: 'notifications', label: 'Notifications', icon: FileClock },
  { to: '/app/ai-copilot', labelKey: 'aiCopilot', label: 'AI Copilot', icon: Sparkles },
  { to: '/app/stock-transfers', labelKey: 'stockTransfers', label: 'Stock Transfers', icon: Boxes },
  { to: '/app/stock-adjustments', labelKey: 'stockAdjustments', label: 'Stock Adjustments', icon: Boxes },
  { to: '/app/inventory-valuation', labelKey: 'inventoryValuation', label: 'Inventory Valuation', icon: Boxes },
  { to: '/app/crm-pipeline', labelKey: 'crmPipeline', label: 'CRM Pipeline', icon: Contact },
  { to: '/app/tasks', labelKey: 'tasks', label: 'Tasks', icon: FileClock },
  { to: '/app/calendar', labelKey: 'calendar', label: 'Calendar', icon: FileClock },
  { to: '/app/attachments', labelKey: 'attachments', label: 'Files', icon: FileClock },
  { to: '/app/billing', labelKey: 'billing', label: 'Billing', icon: Landmark },
  { to: '/app/settings', labelKey: 'settings', label: 'Settings', icon: Settings },
]
