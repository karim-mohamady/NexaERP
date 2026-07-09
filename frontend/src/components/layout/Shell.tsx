import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { Bell, LayoutDashboard, LogOut, Menu, Moon, Search, Sun } from 'lucide-react'
import { modules, utilityLinks } from '../../lib/modules'
import { useAuth } from '../../contexts/AuthContext'
import { useTheme } from '../../contexts/ThemeContext'
import { t } from '../../i18n/translations'

export function Shell() {
  const { user, logout } = useAuth()
  const { theme, setTheme, lang, setLang } = useTheme()
  const navigate = useNavigate()

  async function signOut() {
    await logout()
    navigate('/login')
  }

  const navClass = ({ isActive }: { isActive: boolean }) =>
    `flex items-center gap-3 rounded-md px-3 py-2 text-sm transition ${isActive ? 'bg-slate-950 text-white dark:bg-white dark:text-slate-950' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900'}`

  return (
    <div className="min-h-screen bg-slate-100 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
      <aside className="fixed inset-y-0 start-0 z-30 hidden w-72 border-e border-slate-200 bg-white px-4 py-5 dark:border-slate-800 dark:bg-slate-950 lg:block">
        <div className="flex items-center gap-3 px-2">
          <div className="grid size-10 place-items-center rounded-lg bg-cyan-500 text-lg font-black text-white">N</div>
          <div>
            <p className="font-semibold">NexaERP</p>
            <p className="text-xs text-slate-500">Enterprise command center</p>
          </div>
        </div>
        <nav className="mt-8 space-y-1">
          <NavLink className={navClass} to="/app/dashboard"><LayoutDashboard size={18} />{t(lang, 'dashboard')}</NavLink>
          {modules.map((item) => <NavLink key={item.key} className={navClass} to={`/app/${item.key}`}><item.icon size={18} />{item.label}</NavLink>)}
          {utilityLinks.map((item) => <NavLink key={item.to} className={navClass} to={item.to}><item.icon size={18} />{item.label}</NavLink>)}
        </nav>
      </aside>

      <div className="lg:ps-72">
        <header className="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90">
          <button className="grid size-10 place-items-center rounded-md border border-slate-200 dark:border-slate-800 lg:hidden"><Menu size={18} /></button>
          <div className="hidden min-w-0 flex-1 items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-500 dark:border-slate-800 dark:bg-slate-900 md:flex">
            <Search size={18} />
            <span className="text-sm">Search customers, invoices, products...</span>
          </div>
          <button onClick={() => setLang(lang === 'en' ? 'ar' : 'en')} className="rounded-md border border-slate-200 px-3 py-2 text-sm dark:border-slate-800">{lang.toUpperCase()}</button>
          <button onClick={() => setTheme(theme === 'light' ? 'dark' : 'light')} className="grid size-10 place-items-center rounded-md border border-slate-200 dark:border-slate-800">{theme === 'light' ? <Moon size={18} /> : <Sun size={18} />}</button>
          <button className="grid size-10 place-items-center rounded-md border border-slate-200 dark:border-slate-800"><Bell size={18} /></button>
          <NavLink to="/app/profile" className="hidden items-center gap-3 md:flex">
            <div className="grid size-9 place-items-center rounded-full bg-slate-900 text-sm font-semibold text-white dark:bg-white dark:text-slate-950">{user?.name?.[0] ?? 'A'}</div>
            <div className="text-sm">
              <p className="font-medium">{user?.name}</p>
              <p className="text-xs text-slate-500">{user?.company?.name}</p>
            </div>
          </NavLink>
          <button onClick={signOut} className="grid size-10 place-items-center rounded-md border border-slate-200 dark:border-slate-800" title={t(lang, 'logout')}><LogOut size={18} /></button>
        </header>
        <main className="p-4 md:p-6">
          <Outlet />
        </main>
      </div>
    </div>
  )
}