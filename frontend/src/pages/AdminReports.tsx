import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Download, FileText, AlertCircle, FileDown } from 'lucide-react'
import AdminLayout from '@/components/AdminLayout'
import api from '@/lib/api'
import type { ReportSummary, StudentSummary } from '@/types'

export default function AdminReports() {
  const [report, setReport] = useState<ReportSummary | null>(null)
  const [loading, setLoading] = useState(true)
  const [downloading, setDownloading] = useState(false)
  const [batchDownloading, setBatchDownloading] = useState(false)
  const [month, setMonth] = useState(new Date().getMonth() + 1)
  const [year, setYear] = useState(new Date().getFullYear())
  const [department, setDepartment] = useState('all')

  useEffect(() => {
    fetchReport()
  }, [month, year, department])

  const fetchReport = async () => {
    setLoading(true)
    try {
      const { data } = await api.get(`/reports/summary?month=${month}&year=${year}&department=${department}`)
      setReport(data.data)
    } catch (err) {
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  const downloadCSV = async () => {
    setDownloading(true)
    try {
      const response = await api.get(`/reports/summary?month=${month}&year=${year}&department=${department}`)
      const students = response.data.data.students

      const headers = ['Student ID', 'Name', 'Department', 'Monthly Hours', 'Accumulated', 'Required', 'Completion %', 'Days Present', 'Status']
      const rows = students.map((s: StudentSummary) => [
        s.student_id_no,
        s.name,
        s.department,
        s.monthly_hours,
        s.accumulated_hours,
        s.required_hours,
        s.completion,
        s.days_present,
        s.status
      ])

      const csvContent = [headers, ...rows].map(row => row.join(',')).join('\n')
      const blob = new Blob([csvContent], { type: 'text/csv' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `report_${month}_${year}.csv`)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
    } catch (err) {
      console.error(err)
      alert('Failed to download report')
    } finally {
      setDownloading(false)
    }
  }

  const downloadBatchDTR = async () => {
    setBatchDownloading(true)
    try {
      const students = report?.students || []
      let downloaded = 0
      
      for (const student of students) {
        try {
          const response = await api.get(`/reports/dtr/pdf?student_id_no=${student.student_id_no}&month=${month}&year=${year}`, {
            responseType: 'blob'
          })
          
          const url = window.URL.createObjectURL(new Blob([response.data]))
          const link = document.createElement('a')
          link.href = url
          link.setAttribute('download', `DTR_${student.student_id_no}_${month}_${year}.pdf`)
          document.body.appendChild(link)
          link.click()
          link.remove()
          window.URL.revokeObjectURL(url)
          
          downloaded++
          await new Promise(resolve => setTimeout(resolve, 500))
        } catch (err) {
          console.error(`Failed to download DTR for ${student.student_id_no}`, err)
        }
      }
      
      alert(`Downloaded ${downloaded} of ${students.length} DTRs`)
    } catch (err) {
      console.error(err)
      alert('Failed to download batch DTRs')
    } finally {
      setBatchDownloading(false)
    }
  }

  const departments = ['all', ...new Set(report?.students.map(s => s.department) || [])]

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'ON_TRACK':
        return 'bg-green-100 text-green-700'
      case 'BEHIND':
        return 'bg-red-100 text-red-700'
      default:
        return 'bg-gray-100 text-gray-700'
    }
  }

  return (
    <AdminLayout title="Reports">
      <div className="mb-6">
        <h2 className="text-xl font-semibold mb-4">Monthly Summary Report</h2>

        <div className="flex flex-wrap gap-2 mb-4">
          <select
            value={month}
            onChange={(e) => setMonth(Number(e.target.value))}
            className="px-3 py-2 border border-border rounded-md bg-surface"
          >
            {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12].map(m => (
              <option key={m} value={m}>
                {new Date(2024, m - 1).toLocaleString('default', { month: 'long' })}
              </option>
            ))}
          </select>
          <select
            value={year}
            onChange={(e) => setYear(Number(e.target.value))}
            className="px-3 py-2 border border-border rounded-md bg-surface"
          >
            {[2024, 2025, 2026].map(y => (
              <option key={y} value={y}>{y}</option>
            ))}
          </select>
          <select
            value={department}
            onChange={(e) => setDepartment(e.target.value)}
            className="px-3 py-2 border border-border rounded-md bg-surface"
          >
            <option value="all">All Departments</option>
          </select>
          <button
            onClick={downloadCSV}
            disabled={downloading}
            className="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark disabled:opacity-50"
          >
            <Download size={18} />
            {downloading ? 'Downloading...' : 'Export CSV'}
          </button>
          <button
            onClick={downloadBatchDTR}
            disabled={batchDownloading || !report?.students?.length}
            className="flex items-center gap-2 px-4 py-2 border border-primary text-primary rounded-md hover:bg-primary/10 disabled:opacity-50"
          >
            <FileDown size={18} />
            {batchDownloading ? 'Downloading...' : 'Download All DTRs'}
          </button>
        </div>
      </div>

      {loading ? (
        <div className="text-center py-12">Loading report...</div>
      ) : report ? (
        <>
          <div className="mb-4 text-sm text-text-secondary">
            Period: <span className="font-medium text-text-primary">{report.period}</span>
            {' | '}
            Total Students: <span className="font-medium text-text-primary">{report.students.length}</span>
          </div>

          {report.students.length === 0 ? (
            <div className="bg-surface rounded-lg p-8 text-center">
              <AlertCircle className="mx-auto text-text-secondary mb-2" size={40} />
              <p className="text-text-secondary">No data available for this period</p>
            </div>
          ) : (
            <div className="bg-surface rounded-lg shadow-md overflow-hidden">
              <div className="overflow-x-auto">
                <table className="w-full text-sm">
                  <thead className="bg-background">
                    <tr>
                      <th className="px-4 py-3 text-left">Student ID</th>
                      <th className="px-4 py-3 text-left">Name</th>
                      <th className="px-4 py-3 text-left">Department</th>
                      <th className="px-4 py-3 text-right">Monthly Hrs</th>
                      <th className="px-4 py-3 text-right">Accumulated</th>
                      <th className="px-4 py-3 text-right">Required</th>
                      <th className="px-4 py-3 text-center">Completion</th>
                      <th className="px-4 py-3 text-center">Status</th>
                      <th className="px-4 py-3 text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {report.students.map((student, i) => (
                      <tr key={i} className="border-t border-border hover:bg-background">
                        <td className="px-4 py-3 font-mono text-xs">{student.student_id_no}</td>
                        <td className="px-4 py-3 font-medium">{student.name}</td>
                        <td className="px-4 py-3">{student.department}</td>
                        <td className="px-4 py-3 text-right">{student.monthly_hours}</td>
                        <td className="px-4 py-3 text-right">{student.accumulated_hours}</td>
                        <td className="px-4 py-3 text-right">{student.required_hours}</td>
                        <td className="px-4 py-3 text-center">
                          <span className={`px-2 py-1 rounded text-xs font-medium ${
                            parseFloat(student.completion) >= 80 ? 'bg-green-100 text-green-700' :
                            parseFloat(student.completion) >= 50 ? 'bg-yellow-100 text-yellow-700' :
                            'bg-red-100 text-red-700'
                          }`}>
                            {student.completion}
                          </span>
                        </td>
                        <td className="px-4 py-3 text-center">
                          <span className={`px-2 py-1 rounded text-xs font-medium ${getStatusBadge(student.status)}`}>
                            {student.status}
                          </span>
                        </td>
                        <td className="px-4 py-3 text-center">
                          <Link
                            to={`/admin/students/${student.student_id_no}`}
                            className="text-primary hover:underline"
                          >
                            <FileText size={16} className="inline" />
                          </Link>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </>
      ) : (
        <div className="text-center py-12 text-text-secondary">Failed to load report</div>
      )}
    </AdminLayout>
  )
}
