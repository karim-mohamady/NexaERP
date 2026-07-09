import { useState, type FormEvent } from 'react'
import { api } from '../services/api'
import { useAuth } from '../contexts/AuthContext'

export function ProfilePage() {
  const { user, refresh } = useAuth()
  const [form, setForm] = useState({ name: user?.name ?? '', phone: '', avatar_url: '', locale: 'en', theme: 'light' })

  async function save(event: FormEvent) {
    event.preventDefault()
    await api.put('/auth/profile', form)
    await refresh()
  }

  return (
    <div className="max-w-2xl space-y-5">
      <h1 className="text-3xl font-semibold">Profile</h1>
      <form onSubmit={save} className="space-y-4 rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
        {Object.keys(form).map((field) => <label key={field} className="block text-sm font-medium">{field.replaceAll('_', ' ')}<input value={(form as any)[field]} onChange={(e) => setForm({ ...form, [field]: e.target.value })} className="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 dark:border-slate-800 dark:bg-slate-900" /></label>)}
        <button className="rounded-md bg-slate-950 px-4 py-2 text-white dark:bg-cyan-400 dark:text-slate-950">Update profile</button>
      </form>
    </div>
  )
}
