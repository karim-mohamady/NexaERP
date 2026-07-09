import { useEffect, useState } from 'react'
import type { FormEvent } from 'react'
import { api } from '../services/api'

export function WorkflowPage() {
  const [workflows, setWorkflows] = useState<any[]>([])
  const [form, setForm] = useState({ name: 'Expense approval', module: 'expenses', trigger_type: 'amount_threshold', amount_threshold: '1000', required_role: 'Manager' })

  async function load() {
    const { data } = await api.get('/workflows')
    setWorkflows(data.data ?? [])
  }

  async function save(event: FormEvent) {
    event.preventDefault()
    await api.post('/workflows', { ...form, amount_threshold: Number(form.amount_threshold), steps: [{ name: 'Manager approval', required_role: form.required_role }] })
    await load()
  }

  useEffect(() => { load() }, [])

  return (
    <div className="space-y-5">
      <h1 className="text-3xl font-semibold">Workflow Builder</h1>
      <form onSubmit={save} className="grid gap-3 rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950 md:grid-cols-5">
        {Object.keys(form).map((field) => <input key={field} className="rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800 dark:bg-slate-900" value={(form as any)[field]} onChange={(event) => setForm({ ...form, [field]: event.target.value })} />)}
        <button className="rounded-md bg-slate-950 px-4 py-2 text-white dark:bg-cyan-400 dark:text-slate-950">Create</button>
      </form>
      <div className="grid gap-4 md:grid-cols-2">{workflows.map((workflow) => <div key={workflow.id} className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950"><div className="flex justify-between"><h2 className="font-semibold">{workflow.name}</h2><span className="rounded-full bg-emerald-50 px-2 py-1 text-xs text-emerald-700">{workflow.status}</span></div><p className="mt-2 text-sm text-slate-500">{workflow.module} / {workflow.trigger_type} / {workflow.amount_threshold}</p><div className="mt-4 space-y-2">{workflow.steps?.map((step: any) => <div key={step.id} className="rounded-md bg-slate-50 p-3 text-sm dark:bg-slate-900">{step.approval_order}. {step.name} - {step.required_role}</div>)}</div></div>)}</div>
    </div>
  )
}
