import { Link } from 'react-router-dom'

function RecentPageErrors({ errors }) {
  if (errors.length === 0) {
    return <p className="text-sm text-gray-400">No JS console errors detected.</p>
  }

  return (
    <ul className="divide-y divide-gray-100">
      {errors.map((e) => (
        <li key={e.id} className="flex items-start justify-between gap-3 py-2">
          <div>
            <p className="text-sm font-medium text-gray-900 break-all">{e.message}</p>
            <Link to={`/pages/${e.page_id}`} className="text-xs text-indigo-600 hover:underline">
              {e.page?.url}
            </Link>
            <p className="text-xs text-gray-400">Last seen {new Date(e.last_seen_at).toLocaleString()}</p>
          </div>
          <span className="shrink-0 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
            ×{e.occurrence_count}
          </span>
        </li>
      ))}
    </ul>
  )
}

export default RecentPageErrors
