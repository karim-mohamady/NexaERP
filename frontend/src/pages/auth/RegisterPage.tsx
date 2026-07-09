import { useState, type FormEvent } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../../contexts/AuthContext'

export function RegisterPage() {
  const { register } = useAuth()
  const navigate = useNavigate()
  const [form, setForm] = useState({ name: '', email: '', company_name: '', password: '', password_confirmation: '' })
  const [error, setError] = useState('')

  async function submit(event: FormEvent) {
    event.preventDefault()
    try {
      await register(form)
      navigate('/app/dashboard')
    } catch (err: any) {
      setError(err.response?.data?.message ?? 'Registration failed.')
    }
  }

  return (
    <main className="grid min-h-screen place-items-center bg-slate-100 p-6 dark:bg-slate-950">
      <form onSubmit={submit} className="w-full max-w-lg rounded-lg bg-white p-8 shadow-sm dark:bg-slate-900">
        <h1 className="text-2xl font-semibold">Create NexaERP workspace</h1>
        {error && <p className="mt-4 rounded-md bg-rose-50 p-3 text-sm text-rose-700">{error}</p>}
        {Object.entries({ name: 'Name', email: 'Email', company_name: 'Company', password: 'Password', password_confirmation: 'Confirm password' }).map(([key, label]) => (
          <label key={key} className="mt-4 block text-sm font-medium">{label}<input type={key.includes('password') ? 'password' : 'text'} className="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={(form as any)[key]} onChange={(e) => setForm({ ...form, [key]: e.target.value })} /></label>
        ))}
        <button className="mt-6 w-full rounded-md bg-slate-950 px-4 py-3 font-medium text-white dark:bg-cyan-400 dark:text-slate-950">Create account</button>
        <Link to="/login" className="mt-5 block text-center text-sm text-slate-500">Back to login</Link>
      </form>
    </main>
  )
}
