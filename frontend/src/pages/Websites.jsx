import { useEffect, useState } from 'react'
import apiClient from '../api/client'
import WebsiteTable from '../components/WebsiteTable'

function Websites() {
  const [websites, setWebsites] = useState([])

  useEffect(() => {
    apiClient.get('/websites').then((res) => setWebsites(res.data))
  }, [])

  return (
    <div className="space-y-4">
      <h2 className="text-lg font-medium text-gray-900">Websites</h2>
      <div className="rounded-lg border border-gray-200 bg-white p-4">
        <WebsiteTable websites={websites} />
      </div>
    </div>
  )
}

export default Websites
