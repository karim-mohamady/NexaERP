import { useState, type FormEvent } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Loader2 } from 'lucide-react'
import { useAuth } from '../../contexts/AuthContext'

export function LoginPage() {
  const { login } = useAuth()
  const navigate = useNavigate()
  const [email, setEmail] = useState('admin@nexaerp.com')
  const [password, setPassword] = useState('password')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  async function submit(event: FormEvent) {
    event.preventDefault()
    setError('')
    setLoading(true)
    try {
      await login(email, password)
      navigate('/app/dashboard')
    } catch (err: any) {
      setError(err.response?.data?.message ?? 'Unable to sign in.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <main className="grid min-h-screen bg-slate-950 text-white lg:grid-cols-[1.1fr_0.9fr]">
      <section className="relative hidden overflow-hidden p-10 lg:block">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(6,182,212,.35),transparent_34%),linear-gradient(135deg,#020617,#0f172a_50%,#134e4a)]" />
        <div className="relative flex h-full flex-col justify-between">
          <div className="flex items-center gap-3"><div className="grid size-11 place-items-center rounded-lg bg-cyan-400 font-black text-slate-950">N</div><span className="text-xl font-semibold">NexaERP</span></div>
          <div className="max-w-2xl">
            <p className="mb-4 text-sm uppercase tracking-[.25em] text-cyan-200">Enterprise ERP SaaS</p>
            <h1 className="text-5xl font-semibold leading-tight">Run finance, sales, inventory, HR, and AI insights from one cockpit.</h1>
          </div>
        </div>
      </section>
      <section className="flex items-center justify-center p-6">
        <form onSubmit={submit} className="w-full max-w-md rounded-lg border border-white/10 bg-white p-8 text-slate-950 shadow-2xl dark:bg-slate-900 dark:text-white">
          <h2 className="text-2xl font-semibold">Welcome back</h2>
          <p className="mt-2 text-sm text-slate-500">Demo login: admin@nexaerp.com / password</p>
          {error && <div className="mt-4 rounded-md bg-rose-50 px-3 py-2 text-sm text-rose-700">{error}</div>}
          <label className="mt-6 block text-sm font-medium">Email<input className="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={email} onChange={(e) => setEmail(e.target.value)} /></label>
          <label className="mt-4 block text-sm font-medium">Password<input type="password" className="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={password} onChange={(e) => setPassword(e.target.value)} /></label>
          <button disabled={loading} className="mt-6 flex w-full items-center justify-center gap-2 rounded-md bg-slate-950 px-4 py-3 font-medium text-white dark:bg-cyan-400 dark:text-slate-950">{loading && <Loader2 className="animate-spin" size={18} />}Sign in</button>
          <div className="mt-5 flex justify-between text-sm text-slate-500"><Link to="/forgot-password">Forgot password?</Link><Link to="/register">Create account</Link></div>
        </form>
      </section>
    </main>
  )
}
