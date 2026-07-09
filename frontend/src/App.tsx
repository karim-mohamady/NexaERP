import { Navigate, Route, Routes } from 'react-router-dom'
import { Shell } from './components/layout/Shell'
import { useAuth } from './contexts/AuthContext'
import { LoginPage } from './pages/auth/LoginPage'
import { RegisterPage } from './pages/auth/RegisterPage'
import { SimpleAuthPage } from './pages/auth/SimpleAuthPage'
import { DashboardPage } from './pages/dashboard/DashboardPage'
import { ModulePage } from './pages/ModulePage'
import { ReportsPage } from './pages/ReportsPage'
import { AiPage } from './pages/AiPage'
import { SettingsPage } from './pages/SettingsPage'
import { ProfilePage } from './pages/ProfilePage'

function Protected({ children }: { children: React.ReactNode }) {
  const { user, loading } = useAuth()
  if (loading) return <div className="grid min-h-screen place-items-center bg-slate-100 dark:bg-slate-950">Loading NexaERP...</div>
  if (!user) return <Navigate to="/login" replace />
  return children
}

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Navigate to="/app/dashboard" replace />} />
      <Route path="/login" element={<LoginPage />} />
      <Route path="/register" element={<RegisterPage />} />
      <Route path="/forgot-password" element={<SimpleAuthPage title="Forgot password" />} />
      <Route path="/reset-password" element={<SimpleAuthPage title="Reset password" />} />
      <Route path="/app" element={<Protected><Shell /></Protected>}>
        <Route index element={<Navigate to="/app/dashboard" replace />} />
        <Route path="dashboard" element={<DashboardPage />} />
        <Route path="reports" element={<ReportsPage />} />
        <Route path="ai" element={<AiPage />} />
        <Route path="settings" element={<SettingsPage />} />
        <Route path="profile" element={<ProfilePage />} />
        <Route path=":moduleKey" element={<ModulePage />} />
      </Route>
    </Routes>
  )
}