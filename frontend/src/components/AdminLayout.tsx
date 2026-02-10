import { Link, useLocation, useNavigate } from 'react-router-dom'
import { LogOut, LayoutDashboard, Users, FileBarChart, Settings, Wifi, WifiOff } from 'lucide-react'
import { useAuth } from '@/hooks/useAuth'
import { useWebSocket } from '@/hooks/useWebSocket'
import { ReactNode } from 'react'

interface AdminLayoutProps {
  children: ReactNode
  title?: string
}

export default function AdminLayout({ children, title }: AdminLayoutProps) {
  const { user, logout } = useAuth()
  const { isConnected } = useWebSocket()
  const navigate = useNavigate()
  const location = useLocation()

  const handleLogout = async () => {
    await logout()
    navigate('/login')
  }

  const navItems = [
    { path: '/admin/dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { path: '/admin/students', label: 'Students', icon: Users },
    { path: '/admin/reports', label: 'Reports', icon: FileBarChart },
    { path: '/admin/settings', label: 'Settings', icon: Settings },
  ]

  const isActive = (path: string) => location.pathname === path || location.pathname.startsWith(path + '/')

  return (
    <div className="min-h-screen bg-background">
      <header className="bg-surface shadow-sm px-4 py-3 flex justify-between items-center">
        <div className="flex items-center gap-3">
          <h1 className="font-semibold">{title || 'Admin Panel'}</h1>
        </div>
        <div className="flex items-center gap-4">
          {isConnected ? (
            <span className="flex items-center gap-1 text-success text-sm">
              <Wifi size={16} /> Live
            </span>
          ) : (
            <span className="flex items-center gap-1 text-text-secondary text-sm">
              <WifiOff size={16} /> Offline
            </span>
          )}
          <span className="text-sm text-text-secondary hidden sm:inline">{user?.email}</span>
          <button onClick={handleLogout} className="text-text-secondary hover:text-danger">
            <LogOut size={20} />
          </button>
        </div>
      </header>

      <nav className="bg-surface border-b border-border px-4 py-2 flex gap-1 overflow-x-auto">
        {navItems.map((item) => {
          const Icon = item.icon
          return (
            <Link
              key={item.path}
              to={item.path}
              className={`flex items-center gap-2 px-3 py-2 rounded-md text-sm transition-colors ${
                isActive(item.path)
                  ? 'bg-primary/10 text-primary font-medium'
                  : 'text-text-secondary hover:text-primary hover:bg-background'
              }`}
            >
              <Icon size={18} />
              <span>{item.label}</span>
            </Link>
          )
        })}
      </nav>

      <main className="p-4 max-w-6xl mx-auto">
        {children}
      </main>
    </div>
  )
}
