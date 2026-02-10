import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowLeft, Download } from 'lucide-react'
import api from '@/lib/api'
import type { DTRData } from '@/types'

export default function StudentDTR() {
  const [dtr, setDtr] = useState<DTRData | null>(null)
  const [loading, setLoading] = useState(true)
  const [downloading, setDownloading] = useState(false)
  const [month, setMonth] = useState(new Date().getMonth() + 1)
  const [year, setYear] = useState(new Date().getFullYear())

  useEffect(() => {
    fetchDTR()
  }, [month, year])

  const fetchDTR = async () => {
    setLoading(true)
    try {
      const { data } = await api.get(`/reports/dtr?month=${month}&year=${year}`)
      setDtr(data.data)
    } catch (err) {
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  const downloadPDF = async () => {
    setDownloading(true)
    try {
      const response = await api.get(`/reports/dtr/pdf?month=${month}&year=${year}`, {
        responseType: 'blob'
      })
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `DTR_${month}_${year}.pdf`)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
    } catch (err) {
      console.error(err)
      alert('Failed to download PDF')
    } finally {
      setDownloading(false)
    }
  }

  return (
    <div className="min-h-screen bg-background">
      <header className="bg-surface shadow-sm px-4 py-3 flex items-center gap-3">
        <Link to="/student/dashboard" className="text-text-secondary hover:text-text-primary">
          <ArrowLeft size={24} />
        </Link>
        <h1 className="font-semibold">Daily Time Record</h1>
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
        ) : dtr ? (
          <div className="bg-surface rounded-lg shadow-md p-4">
            <div className="text-center border-b border-border pb-4 mb-4">
              <h2 className="font-bold text-lg">DAILY TIME RECORD</h2>
              <p className="text-sm text-text-secondary">{dtr.period}</p>
            </div>
            
            <div className="mb-4 text-sm">
              <p><strong>Name:</strong> {dtr.student.full_name}</p>
              <p><strong>ID:</strong> {dtr.student.student_id_no}</p>
              <p><strong>Department:</strong> {dtr.student.department}</p>
              <p><strong>Company:</strong> {dtr.student.company || 'N/A'}</p>
            </div>
            
            <div className="overflow-x-auto max-h-96">
              <table className="w-full text-xs border border-border">
                <thead className="bg-background sticky top-0">
                  <tr>
                    <th className="border border-border px-2 py-1">Date</th>
                    <th className="border border-border px-1 py-1">AM In</th>
                    <th className="border border-border px-1 py-1">AM Out</th>
                    <th className="border border-border px-1 py-1">PM In</th>
                    <th className="border border-border px-1 py-1">PM Out</th>
                    <th className="border border-border px-2 py-1">Hrs</th>
                    <th className="border border-border px-2 py-1">Remarks</th>
                  </tr>
                </thead>
                <tbody>
                  {dtr.rows.map((row, i) => (
                    <tr key={i}>
                      <td className="border border-border px-2 py-1 whitespace-nowrap">{row.date.split(',')[0]}</td>
                      <td className="border border-border px-1 py-1 text-center">{row.am_in || ''}</td>
                      <td className="border border-border px-1 py-1 text-center">{row.am_out || ''}</td>
                      <td className="border border-border px-1 py-1 text-center">{row.pm_in || ''}</td>
                      <td className="border border-border px-1 py-1 text-center">{row.pm_out || ''}</td>
                      <td className="border border-border px-2 py-1 text-center">{row.hours_rendered}</td>
                      <td className={`border border-border px-2 py-1 ${
                        row.remarks === 'ABSENT' ? 'text-danger' :
                        row.remarks === 'SATURDAY' || row.remarks === 'SUNDAY' ? 'text-text-secondary' :
                        row.remarks === 'INCOMPLETE' ? 'text-warning' : ''
                      }`}>{row.remarks}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
            
            <div className="mt-4 p-3 bg-background rounded text-sm">
              <p><strong>Monthly Hours:</strong> {dtr.totals.monthly_hours}</p>
              <p><strong>Accumulated:</strong> {dtr.totals.accumulated_hours} / {dtr.totals.required_hours}</p>
              <p><strong>Remaining:</strong> {dtr.totals.remaining_hours} hrs</p>
              <p><strong>Completion:</strong> {dtr.totals.completion_percentage}%</p>
            </div>
            
            <button 
              onClick={downloadPDF}
              disabled={downloading}
              className="w-full mt-4 flex items-center justify-center gap-2 bg-primary text-white py-2 rounded-lg hover:bg-primary-dark disabled:opacity-50">
              <Download size={18} />
              {downloading ? 'Generating...' : 'Download PDF'}
            </button>
          </div>
        ) : (
          <div className="text-center py-8 text-text-secondary">No DTR data available</div>
        )}
      </main>
    </div>
  )
}
