import { useEffect, useState, type FormEvent } from 'react'
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
