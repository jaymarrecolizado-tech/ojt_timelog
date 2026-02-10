import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '@/hooks/useAuth'

export default function Login() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const { login } = useAuth()
  const navigate = useNavigate()

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    
    try {
      await login(email, password)
      navigate('/')
    } catch (err: any) {
      const detail = err.response?.data?.detail
      if (Array.isArray(detail)) {
        setError(detail.map((e: any) => e.msg).join(', '))
      } else if (typeof detail === 'string') {
        setError(detail)
      } else {
        setError('Login failed')
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center p-4 bg-background">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-primary">OJT TLMS</h1>
          <p className="text-text-secondary mt-2">Time Log Management System</p>
        </div>
        
        <form onSubmit={handleSubmit} className="bg-surface rounded-lg shadow-md p-6">
          {error && (
            <div className="mb-4 p-3 bg-red-50 border border-red-200 text-danger rounded-md text-sm">
              {error}
            </div>
          )}
          
          <div className="mb-4">
            <label className="block text-sm font-medium mb-1">Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
              required
            />
          </div>
          
          <div className="mb-6">
            <label className="block text-sm font-medium mb-1">Password</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
              required
            />
          </div>
          
          <button
            type="submit"
            disabled={loading}
            className="w-full bg-primary text-white py-2 rounded-md hover:bg-primary-dark transition-colors disabled:opacity-50"
          >
            {loading ? 'Logging in...' : 'Log In'}
          </button>
          
          <div className="mt-4 text-center text-sm">
            <Link to="/register" className="text-primary hover:underline">
              Don't have an account? Register here
            </Link>
          </div>
        </form>
      </div>
    </div>
  )
}
