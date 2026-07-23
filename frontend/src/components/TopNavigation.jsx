import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

function TopNavigation() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  const handleLogout = async () => {
    await logout()
    navigate('/login')
  }

  return (
    <header className="flex h-14 shrink-0 items-center justify-between border-b border-gray-200 bg-white px-6">
      <h1 className="text-base font-semibold text-gray-900">PageSpeed Monitor</h1>
      <div className="flex items-center gap-4">
        {user && <span className="text-sm text-gray-500">{user.email}</span>}
        <button
          type="button"
          onClick={handleLogout}
          className="text-sm font-medium text-gray-600 hover:text-gray-900"
        >
          Log out
        </button>
      </div>
    </header>
  )
}

export default TopNavigation
