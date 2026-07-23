import { Link } from 'react-router-dom'
import StatusBadge from './StatusBadge'

function WebsiteTable({ websites }) {
  if (websites.length === 0) {
    return <p className="text-sm text-gray-400">No websites yet.</p>
  }

  return (
    <table className="w-full text-left text-sm">
      <thead>
        <tr className="border-b border-gray-200 text-gray-500">
          <th className="py-2 font-medium">Name</th>
          <th className="py-2 font-medium">Environment</th>
          <th className="py-2 font-medium">Schedule</th>
          <th className="py-2 font-medium">Pages</th>
          <th className="py-2 font-medium">Status</th>
          <th className="py-2 font-medium">Uptime</th>
          <th className="py-2 font-medium">Response Time</th>
        </tr>
      </thead>
      <tbody className="divide-y divide-gray-100">
        {websites.map((website) => (
          <tr key={website.id}>
            <td className="py-2">
              <Link to={`/websites/${website.id}`} className="font-medium text-indigo-600 hover:underline">
                {website.name}
              </Link>
              <p className="text-xs text-gray-400">{website.base_url}</p>
            </td>
            <td className="py-2 capitalize text-gray-600">{website.environment}</td>
            <td className="py-2 capitalize text-gray-600">{website.schedule.replaceAll('_', ' ')}</td>
            <td className="py-2 text-gray-600">{website.pages_count}</td>
            <td className="py-2">
              <StatusBadge status={website.enabled ? 'enabled' : 'disabled'} />
            </td>
            <td className="py-2">
              {website.latest_uptime_check ? (
                <StatusBadge status={website.latest_uptime_check.status} />
              ) : (
                <span className="text-xs text-gray-400">Not checked yet</span>
              )}
            </td>
            <td className="py-2 text-gray-600">
              {website.latest_uptime_check?.response_time_ms != null
                ? `${website.latest_uptime_check.response_time_ms} ms`
                : '—'}
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  )
}

export default WebsiteTable
