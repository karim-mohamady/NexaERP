import { useEffect, useMemo, useState, type FormEvent } from 'react'
import { useParams } from 'react-router-dom'
import { FileDown, Plus, Printer, Search, Trash2 } from 'lucide-react'
import { modules, type ModuleConfig } from '../lib/modules'
import { api } from '../services/api'
import { useTheme } from '../contexts/ThemeContext'
import { t } from '../i18n/translations'
import { usePermissions } from '../hooks/usePermissions'

export function ModulePage() {
  const { moduleKey } = useParams()
  const config = useMemo(() => modules.find((item) => item.key === moduleKey) ?? modules[0], [moduleKey])
  const [rows, setRows] = useState<any[]>([])
  const [query, setQuery] = useState('')
  const [formOpen, setFormOpen] = useState(false)
  const [form, setForm] = useState<Record<string, any>>({})
  const [loading, setLoading] = useState(true)
  const { lang } = useTheme()
  const { can } = usePermissions()
  const title = t(lang, config.labelKey)
  const createPermission = config.permission.replace('.view', '.create')
  const deletePermission = config.permission.replace('.view', '.delete')

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

  async function downloadExport(row: any) {
    const route = config.key === 'invoices'
      ? `/exports/invoices/${row.id}/pdf`
      : config.key === 'quotations'
        ? `/exports/quotations/${row.id}/pdf`
        : config.key === 'purchase-orders'
          ? `/exports/purchase-orders/${row.id}/pdf`
          : ''
    if (!route) return
    const { data } = await api.get(route, { responseType: 'blob' })
    const url = URL.createObjectURL(data)
    const link = document.createElement('a')
    link.href = url
    link.download = `${config.key}-${row.number ?? row.id}.pdf`
    link.click()
    URL.revokeObjectURL(url)
  }

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div><p className="text-sm text-slate-500">{t(lang, 'module')}</p><h1 className="text-3xl font-semibold">{title}</h1></div>
        <div className="flex gap-2"><button onClick={() => window.print()} className="grid size-10 place-items-center rounded-md border border-slate-200 dark:border-slate-800" aria-label="Print"><Printer size={18} /></button>{can(createPermission) && <button onClick={() => setFormOpen(true)} className="flex items-center gap-2 rounded-md bg-slate-950 px-4 py-2 text-white dark:bg-cyan-400 dark:text-slate-950"><Plus size={18} />{t(lang, 'create')}</button>}</div>
      </div>
      <div className="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div className="flex flex-wrap items-center gap-3 border-b border-slate-200 p-4 dark:border-slate-800">
          <div className="flex min-w-72 flex-1 items-center gap-2 rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800"><Search size={18} /><input value={query} onChange={(e) => setQuery(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && load()} placeholder={`${t(lang, 'search')} ${title}`} className="w-full bg-transparent outline-none" /></div>
          <button onClick={load} className="rounded-md border border-slate-200 px-4 py-2 dark:border-slate-800">{t(lang, 'filter')}</button>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead><tr className="border-b border-slate-200 text-left text-slate-500 dark:border-slate-800">{config.columns.map((column) => <th key={column} className="px-4 py-3 font-medium">{column.replaceAll('_', ' ')}</th>)}<th className="px-4 py-3" /></tr></thead>
            <tbody>
              {loading ? <tr><td className="px-4 py-8 text-slate-500" colSpan={config.columns.length + 1}>{t(lang, 'loading')}</td></tr> : rows.length === 0 ? <tr><td className="px-4 py-8 text-slate-500" colSpan={config.columns.length + 1}>{t(lang, 'noRecords')}</td></tr> : rows.map((row) => <tr key={row.id} className="border-b border-slate-100 dark:border-slate-900">{config.columns.map((column) => <td key={column} className="max-w-56 truncate px-4 py-3">{String(row[column] ?? '-')}</td>)}<td className="px-4 py-3"><div className="flex justify-end gap-1">{['invoices', 'quotations', 'purchase-orders'].includes(config.key) && can('reports.export') && <button onClick={() => downloadExport(row)} className="grid size-8 place-items-center rounded-md text-cyan-600 hover:bg-cyan-50 dark:hover:bg-cyan-950/30" aria-label="Export"><FileDown size={16} /></button>}{can(deletePermission) && <button onClick={() => remove(row.id)} className="grid size-8 place-items-center rounded-md text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30" aria-label="Delete"><Trash2 size={16} /></button>}</div></td></tr>)}
            </tbody>
          </table>
        </div>
      </div>
      {formOpen && <Drawer config={config} form={form} setForm={setForm} onClose={() => setFormOpen(false)} onSave={save} title={title} />}
    </div>
  )
}

function Drawer({ config, form, setForm, onClose, onSave, title }: { config: ModuleConfig; form: Record<string, any>; setForm: (form: Record<string, any>) => void; onClose: () => void; onSave: (event: FormEvent) => void; title: string }) {
  const { lang } = useTheme()

  return (
    <div className="fixed inset-0 z-50 bg-slate-950/30">
      <form onSubmit={onSave} className="ms-auto h-full w-full max-w-md overflow-y-auto bg-white p-6 shadow-2xl dark:bg-slate-950">
        <div className="flex items-center justify-between"><h2 className="text-xl font-semibold">{t(lang, 'create')} {title}</h2><button type="button" onClick={onClose} className="rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800">{t(lang, 'cancel')}</button></div>
        <div className="mt-6 space-y-4">{config.fields.map((field) => <label key={field.name} className="block text-sm font-medium">{field.label}<input type={field.type ?? 'text'} value={form[field.name] ?? ''} onChange={(e) => setForm({ ...form, [field.name]: field.type === 'number' ? Number(e.target.value) : e.target.value })} className="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800 dark:bg-slate-900" /></label>)}</div>
        <button className="mt-6 w-full rounded-md bg-slate-950 px-4 py-3 font-medium text-white dark:bg-cyan-400 dark:text-slate-950">{t(lang, 'save')}</button>
      </form>
    </div>
  )
}
