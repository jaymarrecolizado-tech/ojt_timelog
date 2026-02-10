import { useEffect, useState } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { ArrowLeft, Save, Download, Plus, Edit2, Clock, Building2 } from 'lucide-react'
import AdminLayout from '@/components/AdminLayout'
import api from '@/lib/api'
import type { StudentDetail, DTRData, ManualEntry } from '@/types'

export default function AdminStudentDetail() {
  const { studentId } = useParams<{ studentId: string }>()
  const navigate = useNavigate()
  const [student, setStudent] = useState<StudentDetail | null>(null)
  const [dtr, setDtr] = useState<DTRData | null>(null)
  const [loading, setLoading] = useState(true)
  const [editing, setEditing] = useState(false)
  const [saving, setSaving] = useState(false)
  const [showManualModal, setShowManualModal] = useState(false)
  const [month, setMonth] = useState(new Date().getMonth() + 1)
  const [year, setYear] = useState(new Date().getFullYear())

  const [editForm, setEditForm] = useState({
    company: '',
    company_address: '',
    supervisor_name: '',
    ojt_start: '',
    ojt_end: '',
    required_hours: 500,
    status: 'active',
  })

  const [manualEntry, setManualEntry] = useState<{
    date: string
    reason: string
    entries: ManualEntry[]
  }>({
    date: new Date().toISOString().split('T')[0],
    reason: '',
    entries: [{ log_type: 'IN', log_category: 'AM', time: '08:00' }],
  })

  useEffect(() => {
    fetchStudent()
  }, [studentId])

  useEffect(() => {
    if (student) {
      fetchDTR()
    }
  }, [student, month, year])

  const fetchStudent = async () => {
    setLoading(true)
    try {
      const { data } = await api.get(`/admin/students?search=${studentId}`)
      const found = data.data.students.find((s: StudentDetail) => s.student_id_no === studentId)
      if (found) {
        const detailRes = await api.get(`/admin/students/${found.id}`)
        setStudent(detailRes.data.data)
        setEditForm({
          company: detailRes.data.data.company || '',
          company_address: detailRes.data.data.company_address || '',
          supervisor_name: detailRes.data.data.supervisor_name || '',
          ojt_start: detailRes.data.data.ojt_start || '',
          ojt_end: detailRes.data.data.ojt_end || '',
          required_hours: detailRes.data.data.required_hours || 500,
          status: detailRes.data.data.status || 'active',
        })
      }
    } catch (err) {
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  const fetchDTR = async () => {
    if (!student) return
    try {
      const { data } = await api.get(`/reports/dtr?student_id=${student.id}&month=${month}&year=${year}`)
      setDtr(data.data)
    } catch (err) {
      console.error(err)
    }
  }

  const handleSave = async () => {
    if (!student) return
    setSaving(true)
    try {
      await api.put(`/admin/students/${student.id}`, editForm)
      setStudent({ ...student, ...editForm })
      setEditing(false)
      alert('Student updated successfully')
    } catch (err) {
      console.error(err)
      alert('Failed to update student')
    } finally {
      setSaving(false)
    }
  }

  const handleDownloadPDF = async () => {
    if (!student) return
    try {
      const response = await api.get(`/reports/dtr/pdf?student_id=${student.id}&month=${month}&year=${year}`, {
        responseType: 'blob',
      })
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `DTR_${student.student_id_no}_${month}_${year}.pdf`)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
    } catch (err) {
      console.error(err)
      alert('Failed to download PDF')
    }
  }

  const handleManualEntry = async () => {
    if (!student || !manualEntry.reason) {
      alert('Please provide a reason for the manual entry')
      return
    }
    try {
      await api.post('/logs/manual', {
        student_id: student.id,
        date: manualEntry.date,
        entries: manualEntry.entries,
        reason: manualEntry.reason,
      })
      setShowManualModal(false)
      setManualEntry({
        date: new Date().toISOString().split('T')[0],
        reason: '',
        entries: [{ log_type: 'IN', log_category: 'AM', time: '08:00' }],
      })
      fetchDTR()
      alert('Manual entry added successfully')
    } catch (err) {
      console.error(err)
      alert('Failed to add manual entry')
    }
  }

  const addEntryRow = () => {
    setManualEntry({
      ...manualEntry,
      entries: [...manualEntry.entries, { log_type: 'IN', log_category: 'AM', time: '08:00' }],
    })
  }

  const removeEntryRow = (index: number) => {
    setManualEntry({
      ...manualEntry,
      entries: manualEntry.entries.filter((_, i) => i !== index),
    })
  }

  const updateEntryRow = (index: number, field: keyof ManualEntry, value: string) => {
    const newEntries = [...manualEntry.entries]
    newEntries[index] = { ...newEntries[index], [field]: value }
    setManualEntry({ ...manualEntry, entries: newEntries })
  }

  if (loading) {
    return (
      <AdminLayout title="Student Detail">
        <div className="text-center py-12">Loading...</div>
      </AdminLayout>
    )
  }

  if (!student) {
    return (
      <AdminLayout title="Student Detail">
        <div className="text-center py-12">
          <p className="text-text-secondary mb-4">Student not found</p>
          <button
            onClick={() => navigate('/admin/students')}
            className="text-primary hover:underline"
          >
            Back to Students
          </button>
        </div>
      </AdminLayout>
    )
  }

  return (
    <AdminLayout title={`${student.first_name} ${student.last_name}`}>
      <button
        onClick={() => navigate('/admin/students')}
        className="flex items-center gap-2 text-text-secondary hover:text-text-primary mb-4"
      >
        <ArrowLeft size={18} />
        Back to Students
      </button>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-1">
          <div className="bg-surface rounded-lg shadow-md p-6">
            <div className="flex justify-between items-start mb-4">
              <h3 className="font-semibold">Student Information</h3>
              {!editing && (
                <button
                  onClick={() => setEditing(true)}
                  className="text-primary hover:bg-primary/10 p-1 rounded"
                >
                  <Edit2 size={18} />
                </button>
              )}
            </div>

            {editing ? (
              <div className="space-y-4">
                <div>
                  <label className="block text-sm text-text-secondary mb-1">Student ID</label>
                  <input
                    type="text"
                    value={student.student_id_no}
                    disabled
                    className="w-full px-3 py-2 border border-border rounded-md bg-background"
                  />
                </div>
                <div>
                  <label className="block text-sm text-text-secondary mb-1">Full Name</label>
                  <input
                    type="text"
                    value={`${student.first_name} ${student.last_name}`}
                    disabled
                    className="w-full px-3 py-2 border border-border rounded-md bg-background"
                  />
                </div>
                <div>
                  <label className="block text-sm text-text-secondary mb-1">Department</label>
                  <input
                    type="text"
                    value={student.department}
                    disabled
                    className="w-full px-3 py-2 border border-border rounded-md bg-background"
                  />
                </div>
                <div>
                  <label className="block text-sm text-text-secondary mb-1">Program</label>
                  <input
                    type="text"
                    value={student.program}
                    disabled
                    className="w-full px-3 py-2 border border-border rounded-md bg-background"
                  />
                </div>
                <div>
                  <label className="block text-sm text-text-secondary mb-1">Company</label>
                  <input
                    type="text"
                    value={editForm.company}
                    onChange={(e) => setEditForm({ ...editForm, company: e.target.value })}
                    className="w-full px-3 py-2 border border-border rounded-md"
                  />
                </div>
                <div>
                  <label className="block text-sm text-text-secondary mb-1">Supervisor</label>
                  <input
                    type="text"
                    value={editForm.supervisor_name}
                    onChange={(e) => setEditForm({ ...editForm, supervisor_name: e.target.value })}
                    className="w-full px-3 py-2 border border-border rounded-md"
                  />
                </div>
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <label className="block text-sm text-text-secondary mb-1">OJT Start</label>
                    <input
                      type="date"
                      value={editForm.ojt_start}
                      onChange={(e) => setEditForm({ ...editForm, ojt_start: e.target.value })}
                      className="w-full px-3 py-2 border border-border rounded-md"
                    />
                  </div>
                  <div>
                    <label className="block text-sm text-text-secondary mb-1">OJT End</label>
                    <input
                      type="date"
                      value={editForm.ojt_end}
                      onChange={(e) => setEditForm({ ...editForm, ojt_end: e.target.value })}
                      className="w-full px-3 py-2 border border-border rounded-md"
                    />
                  </div>
                </div>
                <div>
                  <label className="block text-sm text-text-secondary mb-1">Required Hours</label>
                  <input
                    type="number"
                    value={editForm.required_hours}
                    onChange={(e) => setEditForm({ ...editForm, required_hours: parseInt(e.target.value) })}
                    className="w-full px-3 py-2 border border-border rounded-md"
                  />
                </div>
                <div>
                  <label className="block text-sm text-text-secondary mb-1">Status</label>
                  <select
                    value={editForm.status}
                    onChange={(e) => setEditForm({ ...editForm, status: e.target.value })}
                    className="w-full px-3 py-2 border border-border rounded-md"
                  >
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
                <div className="flex gap-2 pt-2">
                  <button
                    onClick={handleSave}
                    disabled={saving}
                    className="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark disabled:opacity-50"
                  >
                    <Save size={18} />
                    {saving ? 'Saving...' : 'Save'}
                  </button>
                  <button
                    onClick={() => setEditing(false)}
                    className="px-4 py-2 border border-border rounded-md hover:bg-background"
                  >
                    Cancel
                  </button>
                </div>
              </div>
            ) : (
              <div className="space-y-3">
                <InfoRow label="Student ID" value={student.student_id_no} />
                <InfoRow label="Name" value={`${student.first_name} ${student.last_name}`} />
                <InfoRow label="Department" value={student.department} />
                <InfoRow label="Program" value={student.program} />
                <InfoRow label="Company" value={student.company} icon={<Building2 size={14} />} />
                <InfoRow label="OJT Period" value={
                  student.ojt_start && student.ojt_end
                    ? `${student.ojt_start} - ${student.ojt_end}`
                    : 'Not set'
                } />
                <InfoRow label="Required Hours" value={`${student.required_hours} hrs`} />
                <InfoRow
                  label="Status"
                  value={student.status}
                  badge
                  badgeColor={
                    student.status === 'active' ? 'bg-green-100 text-green-700' :
                    student.status === 'completed' ? 'bg-blue-100 text-blue-700' :
                    student.status === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                    'bg-gray-100 text-gray-700'
                  }
                />
              </div>
            )}
          </div>

          <div className="bg-surface rounded-lg shadow-md p-6 mt-4">
            <h3 className="font-semibold mb-3">Quick Actions</h3>
            <div className="space-y-2">
              <button
                onClick={() => setShowManualModal(true)}
                className="w-full flex items-center justify-center gap-2 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark"
              >
                <Plus size={18} />
                Add Manual Entry
              </button>
              <button
                onClick={handleDownloadPDF}
                className="w-full flex items-center justify-center gap-2 px-4 py-2 border border-border rounded-md hover:bg-background"
              >
                <Download size={18} />
                Download DTR PDF
              </button>
            </div>
          </div>
        </div>

        <div className="lg:col-span-2">
          <div className="bg-surface rounded-lg shadow-md p-6">
            <div className="flex justify-between items-center mb-4">
              <h3 className="font-semibold">Time Record</h3>
              <div className="flex gap-2">
                <select
                  value={month}
                  onChange={(e) => setMonth(Number(e.target.value))}
                  className="px-3 py-2 border border-border rounded-md text-sm"
                >
                  {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12].map((m) => (
                    <option key={m} value={m}>
                      {new Date(2024, m - 1).toLocaleString('default', { month: 'long' })}
                    </option>
                  ))}
                </select>
                <select
                  value={year}
                  onChange={(e) => setYear(Number(e.target.value))}
                  className="px-3 py-2 border border-border rounded-md text-sm"
                >
                  {[2024, 2025, 2026].map((y) => (
                    <option key={y} value={y}>
                      {y}
                    </option>
                  ))}
                </select>
              </div>
            </div>

            {dtr ? (
              <>
                <div className="mb-4 p-3 bg-background rounded-md">
                  <div className="grid grid-cols-4 gap-4 text-center text-sm">
                    <div>
                      <p className="text-text-secondary">Monthly</p>
                      <p className="font-bold text-lg">{dtr.totals.monthly_hours}</p>
                    </div>
                    <div>
                      <p className="text-text-secondary">Accumulated</p>
                      <p className="font-bold text-lg">{dtr.totals.accumulated_hours}</p>
                    </div>
                    <div>
                      <p className="text-text-secondary">Required</p>
                      <p className="font-bold text-lg">{dtr.totals.required_hours}</p>
                    </div>
                    <div>
                      <p className="text-text-secondary">Completion</p>
                      <p className="font-bold text-lg text-primary">{dtr.totals.completion_percentage}%</p>
                    </div>
                  </div>
                </div>

                <div className="overflow-x-auto max-h-96">
                  <table className="w-full text-xs">
                    <thead className="bg-background sticky top-0">
                      <tr>
                        <th className="px-2 py-2 text-left">Date</th>
                        <th className="px-1 py-2 text-center">AM In</th>
                        <th className="px-1 py-2 text-center">AM Out</th>
                        <th className="px-1 py-2 text-center">PM In</th>
                        <th className="px-1 py-2 text-center">PM Out</th>
                        <th className="px-1 py-2 text-center">Hrs</th>
                        <th className="px-2 py-2 text-left">Remarks</th>
                      </tr>
                    </thead>
                    <tbody>
                      {dtr.rows.map((row, i) => (
                        <tr key={i} className="border-t border-border">
                          <td className="px-2 py-1 whitespace-nowrap">{row.date.split(',')[0]}</td>
                          <td className="px-1 py-1 text-center">{row.am_in || ''}</td>
                          <td className="px-1 py-1 text-center">{row.am_out || ''}</td>
                          <td className="px-1 py-1 text-center">{row.pm_in || ''}</td>
                          <td className="px-1 py-1 text-center">{row.pm_out || ''}</td>
                          <td className="px-1 py-1 text-center">{row.hours_rendered}</td>
                          <td className={`px-2 py-1 ${
                            row.remarks === 'ABSENT' ? 'text-danger' :
                            row.remarks === 'SATURDAY' || row.remarks === 'SUNDAY' ? 'text-text-secondary' :
                            row.remarks === 'INCOMPLETE' ? 'text-yellow-600' : ''
                          }`}>
                            {row.remarks}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </>
            ) : (
              <div className="text-center py-8 text-text-secondary">No DTR data available</div>
            )}
          </div>
        </div>
      </div>

      {showManualModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-surface rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div className="p-6">
              <h3 className="font-semibold text-lg mb-4">Add Manual Time Entry</h3>

              <div className="space-y-4">
                <div>
                  <label className="block text-sm text-text-secondary mb-1">Date</label>
                  <input
                    type="date"
                    value={manualEntry.date}
                    onChange={(e) => setManualEntry({ ...manualEntry, date: e.target.value })}
                    className="w-full px-3 py-2 border border-border rounded-md"
                  />
                </div>

                <div>
                  <label className="block text-sm text-text-secondary mb-1">Reason</label>
                  <textarea
                    value={manualEntry.reason}
                    onChange={(e) => setManualEntry({ ...manualEntry, reason: e.target.value })}
                    placeholder="Explain why this manual entry is needed..."
                    className="w-full px-3 py-2 border border-border rounded-md resize-none"
                    rows={2}
                  />
                </div>

                <div>
                  <div className="flex justify-between items-center mb-2">
                    <label className="text-sm text-text-secondary">Time Entries</label>
                    <button
                      onClick={addEntryRow}
                      className="text-primary text-sm hover:underline"
                    >
                      + Add Entry
                    </button>
                  </div>
                  <div className="space-y-2">
                    {manualEntry.entries.map((entry, index) => (
                      <div key={index} className="flex gap-2 items-center">
                        <select
                          value={entry.log_type}
                          onChange={(e) => updateEntryRow(index, 'log_type', e.target.value)}
                          className="px-2 py-1 border border-border rounded text-sm"
                        >
                          <option value="IN">IN</option>
                          <option value="OUT">OUT</option>
                        </select>
                        <select
                          value={entry.log_category}
                          onChange={(e) => updateEntryRow(index, 'log_category', e.target.value)}
                          className="px-2 py-1 border border-border rounded text-sm"
                        >
                          <option value="AM">AM</option>
                          <option value="PM">PM</option>
                        </select>
                        <input
                          type="time"
                          value={entry.time}
                          onChange={(e) => updateEntryRow(index, 'time', e.target.value)}
                          className="px-2 py-1 border border-border rounded text-sm flex-1"
                        />
                        {manualEntry.entries.length > 1 && (
                          <button
                            onClick={() => removeEntryRow(index)}
                            className="text-danger hover:bg-red-50 p-1 rounded"
                          >
                            ×
                          </button>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              </div>

              <div className="flex gap-2 mt-6">
                <button
                  onClick={handleManualEntry}
                  className="flex-1 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark"
                >
                  Add Entry
                </button>
                <button
                  onClick={() => setShowManualModal(false)}
                  className="px-4 py-2 border border-border rounded-md hover:bg-background"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </AdminLayout>
  )
}

function InfoRow({ label, value, icon, badge, badgeColor }: {
  label: string
  value: string
  icon?: React.ReactNode
  badge?: boolean
  badgeColor?: string
}) {
  return (
    <div className="flex justify-between items-center">
      <span className="text-sm text-text-secondary flex items-center gap-1">
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
