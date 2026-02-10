import { Routes, Route, Navigate } from 'react-router-dom'
import { useAuth } from '@/hooks/useAuth'
import Login from '@/pages/Login'
import Register from '@/pages/Register'
import StudentDashboard from '@/pages/StudentDashboard'
import ScanQR from '@/pages/ScanQR'
import StudentLogs from '@/pages/StudentLogs'
import StudentDTR from '@/pages/StudentDTR'
import GuardDisplay from '@/pages/GuardDisplay'
import AdminDashboard from '@/pages/AdminDashboard'
import AdminStudents from '@/pages/AdminStudents'

function App() {
  const { user, loading } = useAuth()

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    )
  }

  return (
    <Routes>
      <Route path="/login" element={!user ? <Login /> : <Navigate to={getDashboardPath(user.role)} />} />
      <Route path="/register" element={!user ? <Register /> : <Navigate to={getDashboardPath(user.role)} />} />
      
      <Route path="/student/*" element={user?.role === 'student' ? <StudentRoutes /> : <Navigate to="/login" />} />
      <Route path="/guard/*" element={user?.role === 'guard' ? <GuardRoutes /> : <Navigate to="/login" />} />
      <Route path="/admin/*" element={['admin', 'super_admin'].includes(user?.role || '') ? <AdminRoutes /> : <Navigate to="/login" />} />
      
      <Route path="/" element={<Navigate to={user ? getDashboardPath(user.role) : '/login'} />} />
    </Routes>
  )
}

function getDashboardPath(role: string): string {
  switch (role) {
    case 'student': return '/student/dashboard'
    case 'guard': return '/guard/display'
    case 'admin':
    case 'super_admin': return '/admin/dashboard'
    default: return '/login'
  }
}

function StudentRoutes() {
  return (
    <Routes>
      <Route path="dashboard" element={<StudentDashboard />} />
      <Route path="scan" element={<ScanQR />} />
      <Route path="logs" element={<StudentLogs />} />
      <Route path="dtr" element={<StudentDTR />} />
    </Routes>
  )
}

function GuardRoutes() {
  return (
    <Routes>
      <Route path="display" element={<GuardDisplay />} />
    </Routes>
  )
}

function AdminRoutes() {
  return (
    <Routes>
      <Route path="dashboard" element={<AdminDashboard />} />
      <Route path="students" element={<AdminStudents />} />
    </Routes>
  )
}

export default App
