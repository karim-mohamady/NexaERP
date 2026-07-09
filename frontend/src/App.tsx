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
import { WorkflowPage } from './pages/WorkflowPage'
import { ApprovalPage } from './pages/ApprovalPage'
import { AuditLogsPage } from './pages/AuditLogsPage'
import { NotificationsPage } from './pages/NotificationsPage'
import { AiCopilotPage } from './pages/AiCopilotPage'
import { EnterprisePage } from './pages/EnterprisePage'

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
        <Route path="workflows" element={<WorkflowPage />} />
        <Route path="approvals" element={<ApprovalPage />} />
        <Route path="my-requests" element={<EnterprisePage page="my-requests" />} />
        <Route path="audit-logs" element={<AuditLogsPage />} />
        <Route path="notifications" element={<NotificationsPage />} />
        <Route path="notification-preferences" element={<NotificationsPage />} />
        <Route path="ai-copilot" element={<AiCopilotPage />} />
        <Route path="report-builder" element={<EnterprisePage page="report-builder" />} />
        <Route path="dashboard-builder" element={<EnterprisePage page="dashboard-builder" />} />
        <Route path="stock-transfers" element={<EnterprisePage page="stock-transfers" />} />
        <Route path="stock-adjustments" element={<EnterprisePage page="stock-adjustments" />} />
        <Route path="inventory-valuation" element={<EnterprisePage page="inventory-valuation" />} />
        <Route path="low-stock-alerts" element={<EnterprisePage page="low-stock-alerts" />} />
        <Route path="crm-pipeline" element={<EnterprisePage page="crm-pipeline" />} />
        <Route path="customer-timeline" element={<EnterprisePage page="follow-up-tasks" />} />
        <Route path="follow-up-tasks" element={<EnterprisePage page="follow-up-tasks" />} />
        <Route path="lead-details" element={<EnterprisePage page="crm-pipeline" />} />
        <Route path="leave-requests" element={<Navigate to="/app/attendance" replace />} />
        <Route path="attendance-dashboard" element={<EnterprisePage page="tasks" />} />
        <Route path="payroll-runs" element={<Navigate to="/app/payrolls" replace />} />
        <Route path="employee-documents" element={<EnterprisePage page="attachments" />} />
        <Route path="tasks" element={<EnterprisePage page="tasks" />} />
        <Route path="calendar" element={<EnterprisePage page="calendar" />} />
        <Route path="attachments" element={<EnterprisePage page="attachments" />} />
        <Route path="billing" element={<EnterprisePage page="billing" />} />
        <Route path="usage-limits" element={<EnterprisePage page="usage-limits" />} />
        <Route path="cost-centers" element={<EnterprisePage page="cost-centers" />} />
        <Route path=":moduleKey" element={<ModulePage />} />
      </Route>
    </Routes>
  )
}
