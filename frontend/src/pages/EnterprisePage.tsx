import { useEffect, useMemo, useState } from 'react'
import type { FormEvent } from 'react'
import { Plus, RefreshCw, Trash2 } from 'lucide-react'
import { api } from '../services/api'

type Config = {
  title: string
  endpoint: string
  columns: string[]
  fields: string[]
}

const configs: Record<string, Config> = {
  'my-requests': { title: 'My Requests', endpoint: '/approvals/my-requests', columns: ['module', 'status', 'amount', 'submitted_at'], fields: [] },
  'report-builder': { title: 'Report Builder', endpoint: '/enterprise/saved-reports', columns: ['report_name', 'module', 'group_by', 'sort_by'], fields: ['report_name', 'module', 'group_by', 'sort_by'] },
  'dashboard-builder': { title: 'Dashboard Builder', endpoint: '/enterprise/dashboard-widgets', columns: ['widget_type', 'title', 'position', 'size'], fields: ['widget_type', 'title', 'position', 'size'] },
  'stock-transfers': { title: 'Stock Transfers', endpoint: '/enterprise/stock-transfers', columns: ['product_id', 'from_warehouse_id', 'to_warehouse_id', 'quantity', 'status'], fields: ['product_id', 'from_warehouse_id', 'to_warehouse_id', 'quantity', 'status'] },
  'stock-adjustments': { title: 'Stock Adjustments', endpoint: '/enterprise/stock-adjustments', columns: ['product_id', 'warehouse_id', 'quantity_delta', 'reason', 'status'], fields: ['product_id', 'warehouse_id', 'quantity_delta', 'reason', 'status'] },
  'inventory-valuation': { title: 'Inventory Valuation', endpoint: '/reports/inventory', columns: ['name', 'sku', 'stock_quantity', 'cost_price', 'sale_price'], fields: [] },
  'low-stock-alerts': { title: 'Low Stock Alerts', endpoint: '/products', columns: ['name', 'sku', 'stock_quantity', 'low_stock_threshold'], fields: [] },
  'crm-pipeline': { title: 'CRM Pipeline Kanban', endpoint: '/enterprise/deals', columns: ['title', 'stage', 'value', 'expected_close_date'], fields: ['title', 'stage', 'value', 'expected_close_date'] },
  'follow-up-tasks': { title: 'Follow-up Tasks', endpoint: '/enterprise/tasks', columns: ['title', 'assigned_to', 'due_date', 'priority', 'status'], fields: ['title', 'description', 'due_date', 'priority', 'status'] },
  'tasks': { title: 'Tasks', endpoint: '/enterprise/tasks', columns: ['title', 'due_date', 'priority', 'status'], fields: ['title', 'description', 'due_date', 'priority', 'status'] },
  'calendar': { title: 'Calendar', endpoint: '/enterprise/tasks', columns: ['title', 'due_date', 'priority', 'status'], fields: [] },
  'attachments': { title: 'File Management', endpoint: '/enterprise/attachments', columns: ['file_name', 'attachable_type', 'attachable_id', 'size'], fields: ['file_name', 'file_path', 'attachable_type', 'attachable_id', 'mime_type', 'size'] },
  'billing': { title: 'Billing & Subscription', endpoint: '/enterprise/subscriptions', columns: ['subscription_plan_id', 'status', 'starts_at', 'ends_at'], fields: [] },
  'usage-limits': { title: 'Usage Limits', endpoint: '/enterprise/usage-limits', columns: ['metric', 'limit', 'used'], fields: [] },
  'cost-centers': { title: 'Cost Centers', endpoint: '/enterprise/cost-centers', columns: ['code', 'name', 'is_active'], fields: ['code', 'name'] },
}

export function EnterprisePage({ page }: { page: string }) {
  const config = configs[page] ?? configs.tasks
  const [rows, setRows] = useState<any[]>([])
  const [form, setForm] = useState<Record<string, string>>({})
  const [open, setOpen] = useState(false)
  const [loading, setLoading] = useState(false)

  const normalizedRows = useMemo(() => {
    if (page === 'inventory-valuation') return rows
    if (page === 'low-stock-alerts') return rows.filter((row) => Number(row.stock_quantity) <= Number(row.low_stock_threshold))
    return rows
  }, [page, rows])

  async function load() {
    setLoading(true)
    const { data } = await api.get(config.endpoint)
    setRows(data.data ?? data.rows ?? data)
    setLoading(false)
  }

  async function save(event: FormEvent) {
    event.preventDefault()
    await api.post(config.endpoint, form)
    setOpen(false)
    setForm({})
    await load()
  }

  async function remove(id: number) {
    await api.delete(`${config.endpoint}/${id}`)
    await load()
  }

  useEffect(() => { load() }, [page])

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p className="text-sm text-slate-500">Enterprise</p>
          <h1 className="text-3xl font-semibold">{config.title}</h1>
        </div>
        <div className="flex gap-2">
          <button onClick={load} className="grid size-10 place-items-center rounded-md border border-slate-200 dark:border-slate-800"><RefreshCw size={18} /></button>
          {config.fields.length > 0 && <button onClick={() => setOpen(true)} className="flex items-center gap-2 rounded-md bg-slate-950 px-4 py-2 text-white dark:bg-cyan-400 dark:text-slate-950"><Plus size={18} />Add</button>}
        </div>
      </div>
      <div className="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead><tr className="border-b border-slate-200 text-left text-slate-500 dark:border-slate-800">{config.columns.map((column) => <th key={column} className="px-4 py-3 font-medium">{column.replaceAll('_', ' ')}</th>)}<th /></tr></thead>
            <tbody>
              {loading ? <tr><td className="px-4 py-8 text-slate-500" colSpan={config.columns.length + 1}>Loading...</td></tr> : normalizedRows.length === 0 ? <tr><td className="px-4 py-8 text-slate-500" colSpan={config.columns.length + 1}>No records yet.</td></tr> : normalizedRows.map((row) => <tr key={row.id ?? JSON.stringify(row)} className="border-b border-slate-100 dark:border-slate-900">{config.columns.map((column) => <td key={column} className="max-w-64 truncate px-4 py-3">{String(row[column] ?? '-')}</td>)}<td className="px-4 py-3 text-right">{row.id && config.fields.length > 0 && <button onClick={() => remove(row.id)} className="text-rose-600"><Trash2 size={16} /></button>}</td></tr>)}
            </tbody>
          </table>
        </div>
      </div>
      {open && <div className="fixed inset-0 z-50 bg-slate-950/40"><form onSubmit={save} className="ms-auto h-full w-full max-w-md bg-white p-6 dark:bg-slate-950"><h2 className="text-xl font-semibold">Add {config.title}</h2><div className="mt-5 space-y-3">{config.fields.map((field) => <label key={field} className="block text-sm font-medium">{field.replaceAll('_', ' ')}<input className="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800 dark:bg-slate-900" value={form[field] ?? ''} onChange={(event) => setForm({ ...form, [field]: event.target.value })} /></label>)}</div><div className="mt-6 flex gap-2"><button className="rounded-md bg-slate-950 px-4 py-2 text-white dark:bg-cyan-400 dark:text-slate-950">Save</button><button type="button" onClick={() => setOpen(false)} className="rounded-md border border-slate-200 px-4 py-2 dark:border-slate-800">Cancel</button></div></form></div>}
    </div>
  )
}
