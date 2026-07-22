import { useParams } from 'react-router-dom'

function PageDetails() {
  const { pageId } = useParams()

  return <h2 className="text-lg font-medium text-gray-900">Page #{pageId}</h2>
}

export default PageDetails
