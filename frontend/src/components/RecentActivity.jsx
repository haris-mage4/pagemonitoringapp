import StatusBadge from './StatusBadge'

function RecentActivity({ scans }) {
  if (scans.length === 0) {
    return <p className="text-sm text-gray-400">No scans yet.</p>
  }

  return (
    <ul className="divide-y divide-gray-100">
      {scans.map((scan) => (
        <li key={scan.id} className="flex items-center justify-between py-3">
          <div>
            <p className="text-sm font-medium text-gray-900">{scan.page?.website?.name ?? 'Unknown website'}</p>
            <p className="truncate text-xs text-gray-500">{scan.page?.url}</p>
          </div>
          <div className="flex items-center gap-3">
            <span className="text-xs capitalize text-gray-400">{scan.trigger}</span>
            <StatusBadge status={scan.status} />
          </div>
        </li>
      ))}
    </ul>
  )
}

export default RecentActivity
