import { useEffect, useState } from 'react'
import { api } from '../services/api'

export function ApprovalPage() {
  const [rows, setRows] = useState<any[]>([])
  const [selected, setSelected] = useState<any | null>(null)

  async function load() {
    const { data } = await api.get('/approvals/inbox')
    setRows(data.data ?? [])
  }

  async function act(id: number, action: 'approve' | 'reject' | 'return' | 'cancel') {
    await api.post(`/approvals/${id}/${action}`, { comment: `${action} from approval inbox` })
    await load()
  }

  useEffect(() => { load() }, [])

  return (
    <div className="space-y-5">
      <h1 className="text-3xl font-semibold">Approval Inbox</h1>
      <div className="grid gap-4 lg:grid-cols-[1fr_0.9fr]">
        <div className="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
          {rows.map((row) => <button key={row.id} onClick={() => setSelected(row)} className="block w-full border-b border-slate-100 p-4 text-left hover:bg-slate-50 dark:border-slate-900 dark:hover:bg-slate-900"><div className="flex justify-between"><strong>{row.module}</strong><span>{row.status}</span></div><p className="text-sm text-slate-500">Amount {Number(row.amount).toLocaleString()}</p></button>)}
          {rows.length === 0 && <p className="p-6 text-slate-500">No pending approvals.</p>}
        </div>
        <div className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
          <h2 className="font-semibold">Approval Details</h2>
          {selected ? <div className="mt-4 space-y-3"><p>{selected.comments}</p>{selected.steps?.map((step: any) => <div key={step.id} className="rounded-md bg-slate-50 p-3 text-sm dark:bg-slate-900">{step.required_role} - {step.status}</div>)}<div className="flex flex-wrap gap-2"><button onClick={() => act(selected.id, 'approve')} className="rounded-md bg-emerald-600 px-3 py-2 text-white">Approve</button><button onClick={() => act(selected.id, 'reject')} className="rounded-md bg-rose-600 px-3 py-2 text-white">Reject</button><button onClick={() => act(selected.id, 'return')} className="rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800">Return</button><button onClick={() => act(selected.id, 'cancel')} className="rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800">Cancel</button></div></div> : <p className="mt-4 text-sm text-slate-500">Select a request to view its timeline.</p>}
        </div>
      </div>
    </div>
  )
}
