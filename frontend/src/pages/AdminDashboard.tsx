import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Users, UserCheck, UserX, Clock } from 'lucide-react'
import { useAuth } from '@/hooks/useAuth'
import { useWebSocket } from '@/hooks/useWebSocket'
import { useNavigate } from 'react-router-dom'
import api from '@/lib/api'
import AdminLayout from '@/components/AdminLayout'

interface DashboardStats {
  total_students: number
  present_today: number
  absent_today: number
  late_today: number
}

interface RecentActivity {
  student_name: string
  log_type: string
  log_category: string
  timestamp: string
}

interface ClockedInStudent {
  student_id: string
  student_id_no: string
  name: string
  department: string
  clocked_in_at: string
  category: string
}

export default function AdminDashboard() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const [stats, setStats] = useState<DashboardStats | null>(null)
  const [recentActivity, setRecentActivity] = useState<RecentActivity[]>([])
  const [clockedIn, setClockedIn] = useState<ClockedInStudent[]>([])
  const { isConnected, lastEvent } = useWebSocket()

  useEffect(() => {
    fetchStats()
    fetchClockedIn()
  }, [])

  useEffect(() => {
    if (lastEvent) {
      setRecentActivity(prev => [lastEvent.data, ...prev.slice(0, 9)])
      fetchStats()
      fetchClockedIn()
    }
  }, [lastEvent])

  const fetchStats = async () => {
    try {
      const { data } = await api.get('/admin/dashboard/live')
      setStats(data.data)
    } catch (err) {
      console.error(err)
    }
  }

  const fetchClockedIn = async () => {
    try {
      const { data } = await api.get('/admin/dashboard/clocked-in')
      setClockedIn(data.data.students)
    } catch (err) {
      console.error(err)
    }
  }

  return (
    <AdminLayout title="Dashboard">
      <h2 className="text-xl font-semibold mb-4">Overview</h2>
        
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <div className="bg-surface rounded-lg p-4 shadow-sm">
            <div className="flex items-center gap-2 text-text-secondary text-sm mb-1">
              <Users size={18} />
              <span>Total Students</span>
            </div>
            <p className="text-2xl font-bold">{stats?.total_students || 0}</p>
          </div>
          
          <div className="bg-surface rounded-lg p-4 shadow-sm">
            <div className="flex items-center gap-2 text-success text-sm mb-1">
              <UserCheck size={18} />
              <span>Present Today</span>
            </div>
            <p className="text-2xl font-bold text-success">{stats?.present_today || 0}</p>
          </div>
          
          <div className="bg-surface rounded-lg p-4 shadow-sm">
            <div className="flex items-center gap-2 text-danger text-sm mb-1">
              <UserX size={18} />
              <span>Absent Today</span>
            </div>
            <p className="text-2xl font-bold text-danger">{stats?.absent_today || 0}</p>
          </div>
          
          <div className="bg-surface rounded-lg p-4 shadow-sm">
            <div className="flex items-center gap-2 text-warning text-sm mb-1">
              <Clock size={18} />
              <span>Late Today</span>
            </div>
            <p className="text-2xl font-bold text-warning">{stats?.late_today || 0}</p>
          </div>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="bg-surface rounded-lg p-4 shadow-sm">
            <h3 className="font-semibold mb-3">Currently Clocked In ({clockedIn.length})</h3>
            {clockedIn.length > 0 ? (
              <div className="space-y-2 max-h-60 overflow-y-auto">
                {clockedIn.map((student) => (
                  <div key={student.student_id} className="flex justify-between items-center text-sm py-2 border-b border-border last:border-0">
                    <div>
                      <p className="font-medium">{student.name}</p>
                      <p className="text-text-secondary text-xs">{student.student_id_no} • {student.department}</p>
                    </div>
                    <span className="text-xs text-text-secondary">{student.clocked_in_at}</span>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-text-secondary text-sm">No students currently clocked in</p>
            )}
          </div>
          
          <div className="bg-surface rounded-lg p-4 shadow-sm">
            <h3 className="font-semibold mb-3">Recent Activity</h3>
            {recentActivity.length > 0 ? (
              <div className="space-y-2 max-h-60 overflow-y-auto">
                {recentActivity.map((activity, i) => (
                  <div key={i} className="flex justify-between items-center text-sm py-2 border-b border-border last:border-0">
                    <span>{activity.student_name}</span>
                    <span className={`px-2 py-0.5 rounded text-xs ${
                      activity.log_type === 'IN' ? 'bg-green-100 text-success' : 'bg-blue-100 text-primary'
                    }`}>
                      {activity.log_type} ({activity.log_category})
                    </span>
                    <span className="text-text-secondary">{activity.timestamp}</span>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-text-secondary text-sm">No recent activity. Waiting for clock events...</p>
            )}
          </div>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
          <Link to="/admin/students" className="block bg-surface rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <h4 className="font-medium">Manage Students</h4>
            <p className="text-sm text-text-secondary">View, add, or edit student records</p>
          </Link>
          <Link to="/admin/reports" className="block bg-surface rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <h4 className="font-medium">Generate Reports</h4>
            <p className="text-sm text-text-secondary">DTR and summary reports</p>
          </Link>
        </div>
    </AdminLayout>
  )
}
