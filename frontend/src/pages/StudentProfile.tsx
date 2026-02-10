import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowLeft, User, Building2, Calendar, Clock, Mail, Phone } from 'lucide-react'
import api from '@/lib/api'

interface StudentProfile {
  student_id_no: string
  first_name: string
  middle_name?: string
  last_name: string
  department: string
  program: string
  company?: string
  company_address?: string
  supervisor_name?: string
  ojt_start?: string
  ojt_end?: string
  required_hours: number
  status: string
}

interface HoursSummary {
  accumulated_hours: number
  required_hours: number
  remaining_hours: number
  completion_percentage: number
}

export default function StudentProfile() {
  const [profile, setProfile] = useState<StudentProfile | null>(null)
  const [hoursSummary, setHoursSummary] = useState<HoursSummary | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchProfile()
  }, [])

  const fetchProfile = async () => {
    setLoading(true)
    try {
      const { data } = await api.get('/auth/me')
      if (data.data.student) {
        setProfile(data.data.student)
        
        const today = new Date()
        const { data: dtrData } = await api.get(`/reports/dtr?month=${today.getMonth() + 1}&year=${today.getFullYear()}`)
        if (dtrData.data?.totals) {
          setHoursSummary(dtrData.data.totals)
        }
      }
    } catch (err) {
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        Loading...
      </div>
    )
  }

  if (!profile) {
    return (
      <div className="min-h-screen bg-background p-4">
        <p className="text-text-secondary">Profile not found</p>
        <Link to="/student/dashboard" className="text-primary">Back to Dashboard</Link>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-background">
      <header className="bg-surface shadow-sm px-4 py-3 flex items-center gap-3">
        <Link to="/student/dashboard" className="text-text-secondary hover:text-text-primary">
          <ArrowLeft size={24} />
        </Link>
        <h1 className="font-semibold">My Profile</h1>
      </header>

      <main className="p-4 max-w-lg mx-auto">
        <div className="bg-surface rounded-lg shadow-md p-6 mb-4">
          <div className="flex items-center gap-4 mb-6">
            <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center">
              <User className="text-primary" size={32} />
            </div>
            <div>
              <h2 className="font-semibold text-lg">
                {profile.first_name} {profile.last_name}
              </h2>
              <p className="text-text-secondary text-sm">{profile.student_id_no}</p>
            </div>
          </div>

          <div className="space-y-3">
            <ProfileRow icon={<User size={16} />} label="Full Name" value={`${profile.first_name} ${profile.middle_name ? profile.middle_name + ' ' : ''}${profile.last_name}`} />
            <ProfileRow icon={<Mail size={16} />} label="Department" value={profile.department} />
            <ProfileRow icon={<Building2 size={16} />} label="Program" value={profile.program} />
            <ProfileRow icon={<Building2 size={16} />} label="Company" value={profile.company} />
            {profile.supervisor_name && (
              <ProfileRow icon={<User size={16} />} label="Supervisor" value={profile.supervisor_name} />
            )}
            <ProfileRow icon={<Calendar size={16} />} label="OJT Period" value={
              profile.ojt_start && profile.ojt_end
                ? `${new Date(profile.ojt_start).toLocaleDateString()} - ${new Date(profile.ojt_end).toLocaleDateString()}`
                : 'Not set'
            } />
            <ProfileRow icon={<Clock size={16} />} label="Required Hours" value={`${profile.required_hours} hrs`} />
            <ProfileRow
              icon={<Clock size={16} />}
              label="Status"
              value={profile.status}
              badge
              badgeColor={
                profile.status === 'active' ? 'bg-green-100 text-green-700' :
                profile.status === 'completed' ? 'bg-blue-100 text-blue-700' :
                'bg-yellow-100 text-yellow-700'
              }
            />
          </div>
        </div>

        {hoursSummary && (
          <div className="bg-surface rounded-lg shadow-md p-6">
            <h3 className="font-semibold mb-4">Hours Progress</h3>

            <div className="mb-4">
              <div className="flex justify-between text-sm mb-2">
                <span className="text-text-secondary">Completion</span>
                <span className="font-medium">{hoursSummary.completion_percentage}%</span>
              </div>
              <div className="w-full h-4 bg-background rounded-full overflow-hidden">
                <div
                  className="h-full bg-primary rounded-full transition-all"
                  style={{ width: `${Math.min(hoursSummary.completion_percentage, 100)}%` }}
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4 text-sm">
              <div className="p-3 bg-background rounded-md">
                <p className="text-text-secondary">Accumulated</p>
                <p className="text-xl font-bold text-primary">{hoursSummary.accumulated_hours}</p>
              </div>
              <div className="p-3 bg-background rounded-md">
                <p className="text-text-secondary">Required</p>
                <p className="text-xl font-bold">{hoursSummary.required_hours}</p>
              </div>
              <div className="p-3 bg-background rounded-md col-span-2">
                <p className="text-text-secondary">Remaining</p>
                <p className="text-xl font-bold text-warning">{hoursSummary.remaining_hours} hrs</p>
              </div>
            </div>
          </div>
        )}
      </main>
    </div>
  )
}

function ProfileRow({ icon, label, value, badge, badgeColor }: {
  icon: React.ReactNode
  label: string
  value?: string
  badge?: boolean
  badgeColor?: string
}) {
  return (
    <div className="flex items-center justify-between py-2 border-b border-border last:border-0">
      <span className="text-sm text-text-secondary flex items-center gap-2">
        {icon}
        {label}
      </span>
      {badge ? (
        <span className={`px-2 py-0.5 rounded text-xs font-medium ${badgeColor}`}>
          {value}
        </span>
      ) : (
        <span className="text-sm font-medium">{value || '-'}</span>
      )}
    </div>
  )
}
