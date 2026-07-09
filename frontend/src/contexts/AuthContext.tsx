import { createContext, useContext, useEffect, useMemo, useState } from 'react'
import { api } from '../services/api'

export type User = {
  id: number
  name: string
  email: string
  avatar_url?: string
  company?: { name: string; currency: string }
  roles?: { name: string }[]
}

type AuthContextValue = {
  user: User | null
  permissions: string[]
  loading: boolean
  login: (email: string, password: string) => Promise<void>
  register: (payload: { name: string; email: string; password: string; password_confirmation: string; company_name?: string }) => Promise<void>
  logout: () => Promise<void>
  refresh: () => Promise<void>
}

const AuthContext = createContext<AuthContextValue | null>(null)

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [permissions, setPermissions] = useState<string[]>([])
  const [loading, setLoading] = useState(true)

  async function applySession(data: { token?: string; user: User; permissions?: string[] }) {
    if (data.token) localStorage.setItem('nexa_token', data.token)
    setUser(data.user)
    setPermissions(data.permissions ?? [])
  }

  async function refresh() {
    if (!localStorage.getItem('nexa_token')) {
      setLoading(false)
      return
    }
    try {
      const { data } = await api.get('/auth/me')
      await applySession(data)
    } catch {
      localStorage.removeItem('nexa_token')
      setUser(null)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    refresh()
  }, [])

  async function login(email: string, password: string) {
    const { data } = await api.post('/auth/login', { email, password })
    await applySession(data)
  }

  async function register(payload: { name: string; email: string; password: string; password_confirmation: string; company_name?: string }) {
    const { data } = await api.post('/auth/register', payload)
    await applySession(data)
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } finally {
      localStorage.removeItem('nexa_token')
      setUser(null)
      setPermissions([])
    }
  }

  const value = useMemo(() => ({ user, permissions, loading, login, register, logout, refresh }), [user, permissions, loading])
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (!context) throw new Error('useAuth must be used within AuthProvider')
  return context
}