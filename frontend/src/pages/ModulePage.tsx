import { useEffect, useMemo, useState, type FormEvent } from 'react'
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
