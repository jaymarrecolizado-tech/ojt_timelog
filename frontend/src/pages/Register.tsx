import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import api from '@/lib/api'

export default function Register() {
  const [form, setForm] = useState({
    email: '',
    password: '',
    confirmPassword: '',
    student_id_no: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    department: '',
    program: '',
    contact_no: ''
  })
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const navigate = useNavigate()

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setForm({ ...form, [e.target.name]: e.target.value })
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    
    if (form.password !== form.confirmPassword) {
      setError('Passwords do not match')
      return
    }
    
    if (form.password.length < 8) {
      setError('Password must be at least 8 characters')
      return
    }
    
    setLoading(true)
    
    try {
      await api.post('/auth/register/student', {
        email: form.email,
        password: form.password,
        student_id_no: form.student_id_no,
        first_name: form.first_name,
        middle_name: form.middle_name || undefined,
        last_name: form.last_name,
        department: form.department,
        program: form.program,
        contact_no: form.contact_no || undefined
      })
      navigate('/login', { state: { message: 'Registration successful. Please login.' } })
    } catch (err: any) {
      setError(err.response?.data?.detail || 'Registration failed')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center p-4 bg-background">
      <div className="w-full max-w-lg">
        <div className="text-center mb-6">
          <h1 className="text-3xl font-bold text-primary">Register</h1>
          <p className="text-text-secondary mt-2">Create your student account</p>
        </div>
        
        <form onSubmit={handleSubmit} className="bg-surface rounded-lg shadow-md p-6">
          {error && (
            <div className="mb-4 p-3 bg-red-50 border border-red-200 text-danger rounded-md text-sm">
              {error}
            </div>
          )}
          
          <div className="grid grid-cols-2 gap-4">
            <div className="col-span-2">
              <label className="block text-sm font-medium mb-1">Email</label>
              <input type="email" name="email" value={form.email} onChange={handleChange} className="w-full px-3 py-2 border border-border rounded-md" required />
            </div>
            
            <div>
              <label className="block text-sm font-medium mb-1">Password</label>
              <input type="password" name="password" value={form.password} onChange={handleChange} className="w-full px-3 py-2 border border-border rounded-md" required />
            </div>
            
            <div>
              <label className="block text-sm font-medium mb-1">Confirm Password</label>
              <input type="password" name="confirmPassword" value={form.confirmPassword} onChange={handleChange} className="w-full px-3 py-2 border border-border rounded-md" required />
            </div>
            
            <div className="col-span-2">
              <label className="block text-sm font-medium mb-1">Student ID Number</label>
              <input type="text" name="student_id_no" value={form.student_id_no} onChange={handleChange} className="w-full px-3 py-2 border border-border rounded-md" required />
            </div>
            
            <div>
              <label className="block text-sm font-medium mb-1">First Name</label>
              <input type="text" name="first_name" value={form.first_name} onChange={handleChange} className="w-full px-3 py-2 border border-border rounded-md" required />
            </div>
            
            <div>
              <label className="block text-sm font-medium mb-1">Last Name</label>
              <input type="text" name="last_name" value={form.last_name} onChange={handleChange} className="w-full px-3 py-2 border border-border rounded-md" required />
            </div>
            
            <div>
              <label className="block text-sm font-medium mb-1">Middle Name (Optional)</label>
              <input type="text" name="middle_name" value={form.middle_name} onChange={handleChange} className="w-full px-3 py-2 border border-border rounded-md" />
            </div>
            
            <div>
              <label className="block text-sm font-medium mb-1">Contact Number</label>
              <input type="text" name="contact_no" value={form.contact_no} onChange={handleChange} className="w-full px-3 py-2 border border-border rounded-md" />
            </div>
            
            <div className="col-span-2">
              <label className="block text-sm font-medium mb-1">Department</label>
              <input type="text" name="department" value={form.department} onChange={handleChange} className="w-full px-3 py-2 border border-border rounded-md" required />
            </div>
            
            <div className="col-span-2">
              <label className="block text-sm font-medium mb-1">Program</label>
              <input type="text" name="program" value={form.program} onChange={handleChange} className="w-full px-3 py-2 border border-border rounded-md" required />
            </div>
          </div>
          
          <button type="submit" disabled={loading} className="w-full mt-6 bg-primary text-white py-2 rounded-md hover:bg-primary-dark transition-colors disabled:opacity-50">
            {loading ? 'Registering...' : 'Register'}
          </button>
          
          <div className="mt-4 text-center text-sm">
            <Link to="/login" className="text-primary hover:underline">
              Already have an account? Login here
            </Link>
          </div>
        </form>
      </div>
    </div>
  )
}
