import { Navigate, Outlet } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

function ProtectedRoute() {
  const { token, loading } = useAuth()

  if (loading) {
    return <p className="p-6 text-sm text-gray-400">Loading…</p>
  }

  if (!token) {
    return <Navigate to="/login" replace />
  }

  return <Outlet />
}

export default ProtectedRoute
