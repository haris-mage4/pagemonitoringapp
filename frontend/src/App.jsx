import { createBrowserRouter, RouterProvider } from 'react-router-dom'
import AppLayout from './layouts/AppLayout'
import Dashboard from './pages/Dashboard'
import Websites from './pages/Websites'
import WebsiteDetails from './pages/WebsiteDetails'
import PageDetails from './pages/PageDetails'

const router = createBrowserRouter([
  {
    path: '/',
    element: <AppLayout />,
    children: [
      { index: true, element: <Dashboard /> },
      { path: 'websites', element: <Websites /> },
      { path: 'websites/:websiteId', element: <WebsiteDetails /> },
      { path: 'pages/:pageId', element: <PageDetails /> },
    ],
  },
])

function App() {
  return <RouterProvider router={router} />
}

export default App
