import { useParams } from 'react-router-dom'

function WebsiteDetails() {
  const { websiteId } = useParams()

  return <h2 className="text-lg font-medium text-gray-900">Website #{websiteId}</h2>
}

export default WebsiteDetails
