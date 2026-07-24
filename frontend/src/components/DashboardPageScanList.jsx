import { Link } from 'react-router-dom'
import StatusBadge from './StatusBadge'

function DashboardPageScanList({ pages }) {
  if (pages.length === 0) {
    return <p className="text-sm text-gray-400">No pages registered yet.</p>
  }

  return (
    <ul className="divide-y divide-gray-100">
      {pages.map((page) => (
        <li key={page.id} className="flex items-center justify-between py-3">
          <div>
            <p className="text-sm font-medium text-gray-900">{page.website?.name ?? 'Unknown website'}</p>
            <Link to={`/pages/${page.id}`} className="truncate text-xs text-indigo-600 hover:underline">
              {page.url}
            </Link>
          </div>
          <div className="flex items-center gap-3">
            {page.page_errors_count > 0 && (
              <span className="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                {page.page_errors_count} JS {page.page_errors_count === 1 ? 'error' : 'errors'}
              </span>
            )}
            <span className="text-sm text-gray-600">
              {page.latest_scan?.scan_result?.performance ?? '—'}
            </span>
            {page.latest_scan ? (
              <>
                <span className="text-xs text-gray-400">
                  {new Date(page.latest_scan.finished_at ?? page.latest_scan.created_at).toLocaleString()}
                </span>
                <StatusBadge status={page.latest_scan.status} />
              </>
            ) : (
              <span className="text-xs text-gray-400">Never scanned</span>
            )}
          </div>
        </li>
      ))}
    </ul>
  )
}

export default DashboardPageScanList
