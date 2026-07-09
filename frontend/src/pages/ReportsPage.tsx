import { useEffect, useState } from 'react'
import { api } from '../services/api'
import { StatCard } from '../components/ui/StatCard'

export function ReportsPage() {
  const [data, setData] = useState<any>({})

  useEffect(() => {
    Promise.all([
      api.get('/reports/sales'),
      api.get('/reports/purchases'),
      api.get('/reports/inventory'),
      api.get('/reports/profit-loss'),
    ]).then(([sales, purchases, inventory, profit]) => setData({ sales: sales.data, purchases: purchases.data, inventory: inventory.data, profit: profit.data }))
  }, [])

  return (
    <div className="space-y-5">
      <h1 className="text-3xl font-semibold">Reports</h1>
      <div className="grid gap-4 md:grid-cols-4">
        <StatCard label="Sales" value={data.sales?.total_sales ?? 0} />
        <StatCard label="Purchases" value={data.purchases?.total_purchases ?? 0} />
        <StatCard label="Inventory valuation" value={data.inventory?.valuation ?? 0} />
        <StatCard label="Net profit" value={data.profit?.net_profit ?? 0} />
      </div>
      <div className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
        <h2 className="font-semibold">Export-ready analytics structure</h2>
        <pre className="mt-4 max-h-96 overflow-auto rounded-md bg-slate-950 p-4 text-xs text-slate-100">{JSON.stringify(data, null, 2)}</pre>
      </div>
    </div>
  )
}