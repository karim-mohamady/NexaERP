import { Link } from 'react-router-dom'

export function SimpleAuthPage({ title }: { title: string }) {
  return (
    <main className="grid min-h-screen place-items-center bg-slate-100 p-6 dark:bg-slate-950">
      <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-sm dark:bg-slate-900">
        <h1 className="text-2xl font-semibold">{title}</h1>
        <p className="mt-2 text-sm text-slate-500">The backend endpoint is present and ready for mail delivery integration.</p>
        <input className="mt-6 w-full rounded-md border border-slate-200 px-3 py-2 dark:border-slate-700 dark:bg-slate-950" placeholder="Email address" />
        <button className="mt-4 w-full rounded-md bg-slate-950 px-4 py-3 font-medium text-white dark:bg-cyan-400 dark:text-slate-950">Continue</button>
        <Link to="/login" className="mt-5 block text-center text-sm text-slate-500">Back to login</Link>
      </div>
    </main>
  )
}