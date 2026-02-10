import { useEffect, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '@/hooks/useAuth'
import { Clock, Camera, FileText, Calendar, LogOut, User, TrendingUp } from 'lucide-react'
import api from '@/lib/api'
import type { TodaySummary } from '@/types'

interface HoursSummary {
  accumulated_hours: number
  required_hours: number
  remaining_hours: number
  completion_percentage: number
}

export default function StudentDashboard() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const [todayData, setTodayData] = useState<TodaySummary | null>(null)
  const [hoursSummary, setHoursSummary] = useState<HoursSummary | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchData()
  }, [])

  const fetchData = async () => {
    try {
      const [todayRes, dtrRes] = await Promise.all([
        api.get('/logs/today'),
        api.get(`/reports/dtr?month=${new Date().getMonth() + 1}&year=${new Date().getFullYear()}`)
      ])
      setTodayData(todayRes.data.data)
      if (dtrRes.data.data?.totals) {
        setHoursSummary(dtrRes.data.data.totals)
      }
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
          <Link to="/student/profile" className="text-text-secondary hover:text-primary">
            <User size={20} />
          </Link>
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

        {hoursSummary && (
          <div className="bg-surface rounded-lg shadow-md p-4 mb-4">
            <div className="flex items-center justify-between mb-3">
              <h3 className="font-semibold text-sm flex items-center gap-2">
                <TrendingUp size={16} />
                OJT Progress
              </h3>
              <span className="text-primary font-bold">{hoursSummary.completion_percentage}%</span>
            </div>
            <div className="w-full h-3 bg-background rounded-full overflow-hidden mb-3">
              <div
                className="h-full bg-primary rounded-full transition-all"
                style={{ width: `${Math.min(hoursSummary.completion_percentage, 100)}%` }}
              />
            </div>
            <div className="flex justify-between text-xs text-text-secondary">
              <span>{hoursSummary.accumulated_hours} hrs done</span>
              <span>{hoursSummary.remaining_hours} hrs remaining</span>
            </div>
          </div>
        )}
        
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
          <Link to="/student/profile" className="bg-surface rounded-lg p-4 text-center hover:shadow-md transition-shadow">
            <User className="mx-auto mb-1 text-primary" size={24} />
            <span className="text-sm">Profile</span>
          </Link>
        </div>
      </main>
    </div>
  )
}
