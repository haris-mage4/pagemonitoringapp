import StatusBadge from './StatusBadge'

function WebsiteCard({ website, currentScore, previousScore, nextScheduledScan }) {
  const delta = currentScore != null && previousScore != null ? currentScore - previousScore : null

  return (
    <div className="rounded-lg border border-gray-200 bg-white p-4">
      <div className="flex items-start justify-between">
        <div>
          <h2 className="text-lg font-semibold text-gray-900">{website.name}</h2>
          <p className="text-sm text-gray-500">{website.base_url}</p>
        </div>
        <StatusBadge status={website.enabled ? 'enabled' : 'disabled'} />
      </div>

      <div className="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div>
          <p className="text-xs text-gray-500">Current Score</p>
          <p className="text-xl font-semibold text-gray-900">{currentScore ?? '—'}</p>
        </div>
        <div>
          <p className="text-xs text-gray-500">Previous Score</p>
          <p className="text-xl font-semibold text-gray-900">
            {previousScore ?? '—'}
            {delta !== null && (
              <span className={`ml-2 text-xs font-medium ${delta >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                {delta >= 0 ? '+' : ''}
                {delta}
              </span>
            )}
          </p>
        </div>
        <div>
          <p className="text-xs text-gray-500">Environment</p>
          <p className="text-xl font-semibold capitalize text-gray-900">{website.environment}</p>
        </div>
        <div>
          <p className="text-xs text-gray-500">Next Scheduled Scan</p>
          <p className="text-sm font-medium text-gray-900">
            {nextScheduledScan ? new Date(nextScheduledScan).toLocaleString() : '—'}
          </p>
        </div>
      </div>
    </div>
  )
}

export default WebsiteCard
