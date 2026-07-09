import { useEffect, useState } from 'react'
import { Send, Sparkles } from 'lucide-react'
import { api } from '../services/api'

export function AiPage() {
  const [insights, setInsights] = useState<any>(null)
  const [messages, setMessages] = useState<string[]>(['Ask about sales trends, stock risk, customer segments, or expense anomalies.'])

  useEffect(() => {
    api.get('/ai/insights').then((response) => setInsights(response.data))
  }, [])

  return (
    <div className="space-y-5">
      <div className="flex items-center gap-3"><Sparkles className="text-cyan-500" /><h1 className="text-3xl font-semibold">AI Insights</h1></div>
      {insights && <section className="grid gap-4 md:grid-cols-2">
        {['summary', 'sales_trend', 'inventory_risk', 'customer_segments', 'expense_anomalies'].map((key) => <div key={key} className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950"><p className="text-sm uppercase text-slate-500">{key.replaceAll('_', ' ')}</p><p className="mt-3 text-slate-700 dark:text-slate-200">{insights[key]}</p></div>)}
      </section>}
      <section className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
        <h2 className="font-semibold">Assistant chat</h2>
        <div className="mt-4 space-y-3">{messages.map((message, index) => <div key={index} className="rounded-md bg-slate-100 p-3 text-sm dark:bg-slate-900">{message}</div>)}</div>
        <form className="mt-4 flex gap-2" onSubmit={(event) => { event.preventDefault(); const input = event.currentTarget.elements.namedItem('prompt') as HTMLInputElement; setMessages([...messages, input.value || 'Show next best actions.', 'Mock AI: review low stock, overdue invoices, and expense ratio this week.']); input.value = '' }}>
          <input name="prompt" className="flex-1 rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800 dark:bg-slate-900" placeholder="Ask Nexa AI..." />
          <button className="grid size-10 place-items-center rounded-md bg-slate-950 text-white dark:bg-cyan-400 dark:text-slate-950"><Send size={18} /></button>
        </form>
      </section>
    </div>
  )
}