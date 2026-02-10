import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowLeft } from 'lucide-react'
import api from '@/lib/api'

interface LogDay {
  date: string
  day_name: string
  am_in: string
  am_out: string
  pm_in: string
  pm_out: string
  hours: number
  status: string
}

export default function StudentLogs() {
  const [logs, setLogs] = useState<LogDay[]>([])
  const [loading, setLoading] = useState(true)
  const [month, setMonth] = useState(new Date().getMonth() + 1)
  const [year, setYear] = useState(new Date().getFullYear())

  useEffect(() => {
    fetchLogs()
  }, [month, year])

  const fetchLogs = async () => {
    setLoading(true)
    try {
      const { data } = await api.get(`/logs/range?from_date=${year}-${month}-01&to_date=${year}-${month}-28`)
      setLogs(data.data.days)
    } catch (err) {
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'COMPLETE': return 'text-success'
      case 'INCOMPLETE': return 'text-warning'
      case 'SATURDAY':
      case 'SUNDAY': return 'text-text-secondary'
      case 'ABSENT': return 'text-danger'
      default: return ''
    }
  }

  return (
    <div className="min-h-screen bg-background">
      <header className="bg-surface shadow-sm px-4 py-3 flex items-center gap-3">
        <Link to="/student/dashboard" className="text-text-secondary hover:text-text-primary">
          <ArrowLeft size={24} />
        </Link>
        <h1 className="font-semibold">Time Logs</h1>
      </header>
      
      <main className="p-4">
        <div className="flex gap-2 mb-4">
          <select value={month} onChange={(e) => setMonth(Number(e.target.value))} className="px-3 py-2 border border-border rounded-md">
            {[1,2,3,4,5,6,7,8,9,10,11,12].map(m => (
              <option key={m} value={m}>{new Date(2024, m-1).toLocaleString('default', { month: 'long' })}</option>
            ))}
          </select>
          <select value={year} onChange={(e) => setYear(Number(e.target.value))} className="px-3 py-2 border border-border rounded-md">
            {[2024, 2025, 2026].map(y => (
              <option key={y} value={y}>{y}</option>
            ))}
          </select>
        </div>
        
        {loading ? (
          <div className="text-center py-8">Loading...</div>
        ) : (
          <div className="bg-surface rounded-lg shadow-md overflow-hidden">
            <table className="w-full text-sm">
              <thead className="bg-background">
                <tr>
                  <th className="px-3 py-2 text-left">Date</th>
                  <th className="px-2 py-2 text-center">AM In</th>
                  <th className="px-2 py-2 text-center">AM Out</th>
                  <th className="px-2 py-2 text-center">PM In</th>
                  <th className="px-2 py-2 text-center">PM Out</th>
                  <th className="px-2 py-2 text-center">Hrs</th>
                  <th className="px-3 py-2 text-left">Status</th>
                </tr>
              </thead>
              <tbody>
                {logs.map((log, i) => (
                  <tr key={i} className="border-t border-border">
                    <td className="px-3 py-2">{log.date.split(',')[0]}</td>
                    <td className="px-2 py-2 text-center">{log.am_in || '-'}</td>
                    <td className="px-2 py-2 text-center">{log.am_out || '-'}</td>
                    <td className="px-2 py-2 text-center">{log.pm_in || '-'}</td>
                    <td className="px-2 py-2 text-center">{log.pm_out || '-'}</td>
                    <td className="px-2 py-2 text-center">{log.hours || '-'}</td>
                    <td className={`px-3 py-2 font-medium ${getStatusColor(log.status)}`}>{log.status}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </main>
    </div>
  )
}
