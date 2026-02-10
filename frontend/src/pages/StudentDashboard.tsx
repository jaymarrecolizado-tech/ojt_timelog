import { useEffect, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '@/hooks/useAuth'
import { Clock, Camera, FileText, Calendar, LogOut } from 'lucide-react'
import api from '@/lib/api'
import type { TodaySummary } from '@/types'

export default function StudentDashboard() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const [todayData, setTodayData] = useState<TodaySummary | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchTodayData()
  }, [])

  const fetchTodayData = async () => {
    try {
      const { data } = await api.get('/logs/today')
      setTodayData(data.data)
    } catch (err) {
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  const handleLogout = async () => {
    await logout()
    navigate('/login')
  }

  const getGreeting = () => {
    const hour = new Date().getHours()
    if (hour < 12) return 'Good morning'
    if (hour < 17) return 'Good afternoon'
    return 'Good evening'
  }

  if (loading) {
    return <div className="min-h-screen flex items-center justify-center">Loading...</div>
  }

  return (
    <div className="min-h-screen bg-background">
      <header className="bg-surface shadow-sm px-4 py-3 flex justify-between items-center">
        <div className="flex items-center gap-2">
          <Clock className="text-primary" size={24} />
          <span className="font-semibold">OJT TLMS</span>
        </div>
        <div className="flex items-center gap-3">
          <span className="text-sm">{user?.student?.first_name}</span>
          <button onClick={handleLogout} className="text-text-secondary hover:text-danger">
            <LogOut size={20} />
          </button>
        </div>
      </header>
      
      <main className="p-4 max-w-lg mx-auto">
        <h2 className="text-xl mb-4">{getGreeting()}, {user?.student?.first_name}!</h2>
        
        <div className="bg-surface rounded-lg shadow-md p-4 mb-4">
          <h3 className="font-semibold text-sm text-text-secondary mb-3">TODAY - {todayData?.date || new Date().toLocaleDateString()}</h3>
          
          <div className="mb-3">
            <span className="text-sm">Status: </span>
            <span className="font-semibold">{todayData?.current_status || 'Not clocked in'}</span>
          </div>
          
          <div className="grid grid-cols-2 gap-2 text-sm">
            <div className="flex justify-between p-2 bg-background rounded">
              <span>AM IN</span>
              <span>{todayData?.logs.find(l => l.log_category === 'AM' && l.log_type === 'IN')?.formatted_time || '--'}</span>
            </div>
            <div className="flex justify-between p-2 bg-background rounded">
              <span>AM OUT</span>
              <span>{todayData?.logs.find(l => l.log_category === 'AM' && l.log_type === 'OUT')?.formatted_time || '--'}</span>
            </div>
            <div className="flex justify-between p-2 bg-background rounded">
              <span>PM IN</span>
              <span>{todayData?.logs.find(l => l.log_category === 'PM' && l.log_type === 'IN')?.formatted_time || '--'}</span>
            </div>
            <div className="flex justify-between p-2 bg-background rounded">
              <span>PM OUT</span>
              <span>{todayData?.logs.find(l => l.log_category === 'PM' && l.log_type === 'OUT')?.formatted_time || '--'}</span>
            </div>
          </div>
          
          <div className="mt-3 text-sm">
            Hours today: <span className="font-semibold">{todayData?.hours_today || 0}</span>
          </div>
        </div>
        
        <Link to="/student/scan" className="block">
          <button className="w-full bg-primary text-white py-4 rounded-lg text-lg font-semibold hover:bg-primary-dark transition-colors flex items-center justify-center gap-2">
            <Camera size={24} />
            SCAN QR
          </button>
        </Link>
        
        <div className="grid grid-cols-3 gap-2 mt-4">
          <Link to="/student/logs" className="bg-surface rounded-lg p-4 text-center hover:shadow-md transition-shadow">
            <Calendar className="mx-auto mb-1 text-primary" size={24} />
            <span className="text-sm">Logs</span>
          </Link>
          <Link to="/student/dtr" className="bg-surface rounded-lg p-4 text-center hover:shadow-md transition-shadow">
            <FileText className="mx-auto mb-1 text-primary" size={24} />
            <span className="text-sm">DTR</span>
          </Link>
          <div className="bg-surface rounded-lg p-4 text-center">
            <Clock className="mx-auto mb-1 text-primary" size={24} />
            <span className="text-sm">Hours</span>
          </div>
        </div>
      </main>
    </div>
  )
}
