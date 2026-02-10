import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Search, LogOut } from 'lucide-react'
import { useAuth } from '@/hooks/useAuth'
import { useNavigate } from 'react-router-dom'
import api from '@/lib/api'

interface Student {
  id: string
  student_id_no: string
  full_name: string
  department: string
  company: string
  status: string
}

export default function AdminStudents() {
  const { logout } = useAuth()
  const navigate = useNavigate()
  const [students, setStudents] = useState<Student[]>([])
  const [search, setSearch] = useState('')
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchStudents()
  }, [])

  const fetchStudents = async () => {
    setLoading(true)
    try {
      const { data } = await api.get('/admin/students')
      setStudents(data.data.students)
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

  const filtered = students.filter(s => 
    s.full_name.toLowerCase().includes(search.toLowerCase()) ||
    s.student_id_no.toLowerCase().includes(search.toLowerCase())
  )

  return (
    <div className="min-h-screen bg-background">
      <header className="bg-surface shadow-sm px-4 py-3 flex justify-between items-center">
        <h1 className="font-semibold">Students</h1>
        <button onClick={handleLogout} className="text-text-secondary hover:text-danger">
          <LogOut size={20} />
        </button>
      </header>
      
      <nav className="bg-surface border-b border-border px-4 py-2 flex gap-4 text-sm">
        <Link to="/admin/dashboard" className="text-text-secondary hover:text-primary">Dashboard</Link>
        <Link to="/admin/students" className="text-primary font-medium">Students</Link>
        <Link to="/admin/reports" className="text-text-secondary hover:text-primary">Reports</Link>
        <Link to="/admin/settings" className="text-text-secondary hover:text-primary">Settings</Link>
      </nav>
      
      <main className="p-4 max-w-4xl mx-auto">
        <div className="flex items-center gap-2 mb-4">
          <div className="relative flex-1">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary" size={18} />
            <input
              type="text"
              placeholder="Search by name or ID..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="w-full pl-10 pr-4 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
            />
          </div>
        </div>
        
        {loading ? (
          <div className="text-center py-8">Loading...</div>
        ) : (
          <div className="bg-surface rounded-lg shadow-md overflow-hidden">
            <table className="w-full text-sm">
              <thead className="bg-background">
                <tr>
                  <th className="px-4 py-3 text-left">ID No.</th>
                  <th className="px-4 py-3 text-left">Name</th>
                  <th className="px-4 py-3 text-left">Department</th>
                  <th className="px-4 py-3 text-left">Company</th>
                  <th className="px-4 py-3 text-left">Status</th>
                </tr>
              </thead>
              <tbody>
                {filtered.map((student) => (
                  <tr key={student.id} className="border-t border-border hover:bg-background">
                    <td className="px-4 py-3 font-medium">{student.student_id_no}</td>
                    <td className="px-4 py-3">{student.full_name}</td>
                    <td className="px-4 py-3">{student.department}</td>
                    <td className="px-4 py-3">{student.company || '-'}</td>
                    <td className="px-4 py-3">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                        student.status === 'active' ? 'bg-green-100 text-success' :
                        student.status === 'completed' ? 'bg-blue-100 text-primary' :
                        'bg-gray-100 text-text-secondary'
                      }`}>
                        {student.status}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            
            {filtered.length === 0 && (
              <div className="text-center py-8 text-text-secondary">
                No students found
              </div>
            )}
          </div>
        )}
      </main>
    </div>
  )
}
