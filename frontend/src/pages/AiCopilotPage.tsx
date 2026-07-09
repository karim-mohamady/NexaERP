import { useEffect, useState } from 'react'
import type { FormEvent } from 'react'
import { Send, Sparkles } from 'lucide-react'
import { api } from '../services/api'

export function AiCopilotPage() {
  const [message, setMessage] = useState('What should we focus on this week?')
  const [insight, setInsight] = useState<any>(null)
  const suggestions = ['Why did sales decrease this month?', 'Which products are at risk of stockout?', 'Are there unusual expenses?', 'Which customers are most valuable?']

  async function ask(event?: FormEvent) {
    event?.preventDefault()
    const { data } = await api.post('/ai/chat', { message })
    setInsight(data)
  }

  useEffect(() => { ask() }, [])

  return (
    <div className="space-y-5">
      <div className="flex items-center gap-2"><Sparkles className="text-cyan-500" /><h1 className="text-3xl font-semibold">AI Copilot</h1></div>
      <div className="grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
        <div className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
          <h2 className="font-semibold">Suggested questions</h2>
          <div className="mt-4 space-y-2">{suggestions.map((item) => <button key={item} onClick={() => setMessage(item)} className="block w-full rounded-md bg-slate-50 p-3 text-left text-sm hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800">{item}</button>)}</div>
        </div>
        <form onSubmit={ask} className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
          <textarea value={message} onChange={(event) => setMessage(event.target.value)} className="h-28 w-full rounded-md border border-slate-200 p-3 dark:border-slate-800 dark:bg-slate-900" />
          <button className="mt-3 flex items-center gap-2 rounded-md bg-slate-950 px-4 py-2 text-white dark:bg-cyan-400 dark:text-slate-950"><Send size={18} />Ask Copilot</button>
          {insight && <div className="mt-5 space-y-3"><p className="text-lg font-semibold">{insight.answer}</p><div className="grid gap-3 md:grid-cols-3">{Object.entries(insight.signals ?? {}).map(([key, value]) => <div key={key} className="rounded-md bg-slate-50 p-3 dark:bg-slate-900"><p className="text-xs text-slate-500">{key}</p><p className="font-semibold">{Array.isArray(value) ? value.join(', ') : String(value)}</p></div>)}</div>{insight.recommendations?.map((item: string) => <p key={item} className="rounded-md border border-cyan-200 bg-cyan-50 p-3 text-sm text-cyan-900 dark:border-cyan-900 dark:bg-cyan-950 dark:text-cyan-100">{item}</p>)}</div>}
        </form>
      </div>
    </div>
  )
}
