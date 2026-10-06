import { Link, useLocation, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function Navbar() {
  const { user, logout } = useAuth()
  const location = useLocation()
  const navigate = useNavigate()

  if (!user) return null

  const links = {
    user: [
      { to: '/', label: 'My Requests' },
      { to: '/new-request', label: 'New Request' },
    ],
    admin: [
      { to: '/', label: 'Dashboard' },
      { to: '/requests', label: 'All Requests' },
    ],
    technician: [
      { to: '/', label: 'My Assignments' },
    ],
  }[user.role]

  const handleLogout = async () => {
    await logout()
    navigate('/login')
  }

  return (
    <nav className="navbar">
      <div className="navbar-brand">🛠️ Service Request System</div>
      <div className="navbar-links">
        {links.map((l) => (
          <Link key={l.to} to={l.to} className={location.pathname === l.to ? 'active' : ''}>
            {l.label}
          </Link>
        ))}
      </div>
      <div className="navbar-user">
        <span className="badge-role">{user.role}</span>
        <span>{user.name}</span>
        <button className="btn btn-secondary btn-sm" onClick={handleLogout}>Logout</button>
      </div>
    </nav>
  )
}
