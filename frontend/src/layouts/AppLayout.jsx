import { Outlet } from 'react-router-dom'
import Sidebar from '../components/Sidebar'
import TopNavigation from '../components/TopNavigation'

function AppLayout() {
  return (
    <div className="flex min-h-screen flex-col">
      <TopNavigation />
      <div className="flex flex-1">
        <Sidebar />
        <main className="flex-1 bg-gray-50 p-6">
          <Outlet />
        </main>
      </div>
    </div>
  )
}

export default AppLayout
