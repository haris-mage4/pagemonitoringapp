import StatusBadge from './StatusBadge'

function WebsiteCard({ website, currentScore, previousScore, nextScheduledScan, onScan, scanning, scanMessage, onEdit }) {
  const delta = currentScore != null && previousScore != null ? currentScore - previousScore : null

  return (
    <div className="rounded-lg border border-gray-200 bg-white p-4">
      <div className="flex items-start justify-between">
        <div>
          <h2 className="text-lg font-semibold text-gray-900">{website.name}</h2>
          <p className="text-sm text-gray-500">{website.base_url}</p>
        </div>
        <div className="flex items-center gap-3">
          {scanMessage && <span className="text-xs text-gray-500">{scanMessage}</span>}
          {onEdit && (
            <button
              type="button"
              onClick={onEdit}
              className="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
            >
              Edit
            </button>
          )}
          {onScan && (
            <button
              type="button"
              onClick={onScan}
              disabled={scanning}
              className="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
            >
              {scanning ? 'Scanning…' : 'Scan Now'}
            </button>
          )}
          <StatusBadge status={website.enabled ? 'enabled' : 'disabled'} />
        </div>
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
