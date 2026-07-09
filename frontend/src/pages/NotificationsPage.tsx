import { useEffect, useState } from 'react'
import { api } from '../services/api'

export function NotificationsPage() {
  const [items, setItems] = useState<any[]>([])

  async function load() {
    const { data } = await api.get('/notifications')
    setItems(data.data ?? [])
  }

  async function markAll() {
    await api.post('/notifications/mark-all-read')
    await load()
  }

  useEffect(() => { load() }, [])

  return (
    <div className="space-y-5">
      <div className="flex justify-between"><h1 className="text-3xl font-semibold">Notifications</h1><button onClick={markAll} className="rounded-md border border-slate-200 px-4 py-2 dark:border-slate-800">Mark all read</button></div>
      <div className="grid gap-3">{items.map((item) => <div key={item.id} className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950"><div className="flex justify-between"><strong>{item.title}</strong><span className="text-xs text-slate-500">{item.type}</span></div><p className="mt-2 text-sm text-slate-500">{item.body}</p></div>)}</div>
    </div>
  )
}
