import { useEffect, useState } from 'react'
import { Area, AreaChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts'
import { Sparkles } from 'lucide-react'
import { api } from '../../services/api'
import { StatCard } from '../../components/ui/StatCard'

export function DashboardPage() {
  const [data, setData] = useState<any>(null)

  useEffect(() => {
    api.get('/dashboard/summary').then((response) => setData(response.data))
  }, [])

  if (!data) return <div className="grid gap-4 md:grid-cols-4">{Array.from({ length: 8 }).map((_, i) => <div key={i} className="h-32 animate-pulse rounded-lg bg-white dark:bg-slate-900" />)}</div>

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div><p className="text-sm text-slate-500">Today</p><h1 className="text-3xl font-semibold">Executive Dashboard</h1></div>
        <div className="flex gap-2"><input type="date" className="rounded-md border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900" /><input type="date" className="rounded-md border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900" /></div>
      </div>
      <section className="grid gap-4 md:grid-cols-4">{data.kpis.map((kpi: any) => <StatCard key={kpi.label} {...kpi} />)}</section>
      <section className="grid gap-4 xl:grid-cols-[1.6fr_0.9fr]">
        <div className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
          <h2 className="font-semibold">Revenue, expenses, profit</h2>
          <div className="mt-4 h-80">
            <ResponsiveContainer width="100%" height="100%">
              <AreaChart data={data.series}>
                <defs><linearGradient id="revenue" x1="0" y1="0" x2="0" y2="1"><stop offset="5%" stopColor="#06b6d4" stopOpacity={0.4}/><stop offset="95%" stopColor="#06b6d4" stopOpacity={0}/></linearGradient></defs>
                <CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" />
                <XAxis dataKey="month" /><YAxis /><Tooltip />
                <Area dataKey="revenue" stroke="#06b6d4" fill="url(#revenue)" />
                <Area dataKey="expenses" stroke="#f43f5e" fill="transparent" />
                <Area dataKey="profit" stroke="#10b981" fill="transparent" />
              </AreaChart>
            </ResponsiveContainer>
          </div>
        </div>
        <div className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
          <div className="flex items-center gap-2"><Sparkles size={18} className="text-cyan-500" /><h2 className="font-semibold">AI insights</h2></div>
          <div className="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">{data.notifications.map((item: any) => <div key={item.title} className="rounded-md bg-slate-50 p-3 dark:bg-slate-900"><strong className="block text-slate-950 dark:text-white">{item.title}</strong>{item.body}</div>)}</div>
        </div>
      </section>
      <section className="grid gap-4 lg:grid-cols-3">
        <Panel title="Recent invoices" rows={data.recent_invoices} columns={['number', 'status', 'grand_total']} />
        <Panel title="Recent customers" rows={data.recent_customers} columns={['name', 'email', 'status']} />
        <Panel title="Inventory alerts" rows={data.low_stock} columns={['name', 'sku', 'stock_quantity']} />
      </section>
    </div>
  )
}

function Panel({ title, rows, columns }: { title: string; rows: any[]; columns: string[] }) {
  return (
    <div className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
      <h2 className="font-semibold">{title}</h2>
      <div className="mt-4 space-y-2">{rows.map((row) => <div key={row.id} className="grid grid-cols-3 gap-2 rounded-md bg-slate-50 p-3 text-sm dark:bg-slate-900">{columns.map((column) => <span key={column} className="truncate">{row[column]}</span>)}</div>)}</div>
    </div>
  )
}