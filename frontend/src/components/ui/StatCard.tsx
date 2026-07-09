export function StatCard({ label, value, change }: { label: string; value: string | number; change?: number }) {
  return (
    <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
      <p className="text-sm text-slate-500 dark:text-slate-400">{label}</p>
      <div className="mt-3 flex items-end justify-between gap-3">
        <strong className="text-2xl font-semibold text-slate-950 dark:text-white">{typeof value === 'number' ? value.toLocaleString() : value}</strong>
        {change !== undefined && <span className={change >= 0 ? 'text-sm text-emerald-600' : 'text-sm text-rose-600'}>{change >= 0 ? '+' : ''}{change}%</span>}
      </div>
    </div>
  )
}