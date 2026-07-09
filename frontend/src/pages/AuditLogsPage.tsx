import { useEffect, useState } from 'react'
import { api } from '../services/api'

export function AuditLogsPage() {
  const [rows, setRows] = useState<any[]>([])
  const [selected, setSelected] = useState<any | null>(null)

  useEffect(() => { api.get('/audit-logs').then(({ data }) => setRows(data.data ?? [])) }, [])

  return (
    <div className="space-y-5">
      <h1 className="text-3xl font-semibold">Audit Logs</h1>
      <div className="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <table className="w-full text-sm"><thead><tr className="border-b border-slate-200 text-left text-slate-500 dark:border-slate-800"><th className="p-3">Module</th><th>Action</th><th>User</th><th>Date</th></tr></thead><tbody>{rows.map((row) => <tr key={row.id} onClick={() => setSelected(row)} className="cursor-pointer border-b border-slate-100 hover:bg-slate-50 dark:border-slate-900 dark:hover:bg-slate-900"><td className="p-3">{row.module}</td><td>{row.action}</td><td>{row.user?.name ?? '-'}</td><td>{row.created_at}</td></tr>)}</tbody></table>
      </div>
      {selected && <div className="fixed inset-0 z-50 grid place-items-center bg-slate-950/40 p-4"><div className="max-h-[80vh] w-full max-w-3xl overflow-auto rounded-lg bg-white p-5 dark:bg-slate-950"><div className="flex justify-between"><h2 className="font-semibold">Before / After</h2><button onClick={() => setSelected(null)}>Close</button></div><pre className="mt-4 rounded-md bg-slate-950 p-4 text-xs text-slate-100">{JSON.stringify({ old: selected.old_values, new: selected.new_values }, null, 2)}</pre></div></div>}
    </div>
  )
}
