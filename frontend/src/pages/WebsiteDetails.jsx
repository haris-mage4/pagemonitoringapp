import { useCallback, useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import apiClient from '../api/client'
import WebsiteCard from '../components/WebsiteCard'
import TrendChart from '../components/TrendChart'
import StatusBadge from '../components/StatusBadge'
import AddPageForm from '../components/AddPageForm'

function WebsiteDetails() {
  const { websiteId } = useParams()
  const [details, setDetails] = useState(null)
  const [error, setError] = useState(null)
  const [scanning, setScanning] = useState(false)
  const [scanMessage, setScanMessage] = useState(null)

  const load = useCallback(() => {
    setError(null)
    apiClient
      .get(`/websites/${websiteId}`)
      .then((res) => setDetails(res.data))
      .catch(() => setError('Could not load this website.'))
  }, [websiteId])

  useEffect(() => {
    load()
  }, [load])

  const handlePageCreated = (page) => {
    setDetails((current) => ({ ...current, pages: [...current.pages, page] }))
  }

  const handleScan = () => {
    setScanning(true)
    setScanMessage(null)
    apiClient
      .post(`/websites/${websiteId}/scan`)
      .then(() => setScanMessage('Scan queued.'))
      .catch(() => setScanMessage('Failed to queue scan.'))
      .finally(() => setScanning(false))
  }

  if (error) {
    return <p className="text-sm text-red-600">{error}</p>
  }

  if (!details) {
    return <p className="text-sm text-gray-400">Loading…</p>
  }

  const { website, pages, latest_scan: latestScan, current_score: currentScore,
    previous_score: previousScore, performance_history: performanceHistory,
    next_scheduled_scan: nextScheduledScan, latest_uptime_check: latestUptimeCheck } = details

  const chartData = performanceHistory.map((row) => ({ scanned_at: row.scanned_at, value: row.performance }))

  return (
    <div className="space-y-6">
      <WebsiteCard
        website={website}
        currentScore={currentScore}
        previousScore={previousScore}
        nextScheduledScan={nextScheduledScan}
        onScan={handleScan}
        scanning={scanning}
        scanMessage={scanMessage}
      />

      <TrendChart title="Performance History" data={chartData} />

      <div className="rounded-lg border border-gray-200 bg-white p-4">
        <h3 className="mb-2 text-sm font-medium text-gray-900">
          Latest Scan{latestScan ? ` — ${new Date(latestScan.finished_at).toLocaleString()}` : ''}
        </h3>
        {latestScan ? (
          <p className="text-sm text-gray-600">
            {latestScan.page?.url} <StatusBadge status={latestScan.status} />
          </p>
        ) : (
          <p className="text-sm text-gray-400">No scans yet.</p>
        )}
      </div>

      <div className="rounded-lg border border-gray-200 bg-white p-4">
        <h3 className="mb-2 text-sm font-medium text-gray-900">Uptime</h3>
        {latestUptimeCheck ? (
          <div className="flex items-center gap-4 text-sm text-gray-600">
            <StatusBadge status={latestUptimeCheck.status} />
            <span>Checked {new Date(latestUptimeCheck.checked_at).toLocaleString()}</span>
            <span>{latestUptimeCheck.response_time_ms} ms</span>
          </div>
        ) : (
          <p className="text-sm text-gray-400">Not checked yet.</p>
        )}
      </div>

      <div className="rounded-lg border border-gray-200 bg-white p-4">
        <h3 className="mb-2 text-sm font-medium text-gray-900">Pages</h3>
        <AddPageForm websiteId={website.id} onCreated={handlePageCreated} />
        {pages.length === 0 ? (
          <p className="text-sm text-gray-400">No pages yet.</p>
        ) : (
          <ul className="divide-y divide-gray-100">
            {pages.map((page) => (
              <li key={page.id} className="flex items-center justify-between py-2">
                <div>
                  <Link to={`/pages/${page.id}`} className="text-sm font-medium text-indigo-600 hover:underline">
                    {page.url}
                  </Link>
                  <p className="text-xs capitalize text-gray-400">{page.page_type}</p>
                </div>
                <div className="flex items-center gap-3">
                  <span className="text-sm text-gray-600">
                    {page.latest_scan?.scan_result?.performance ?? '—'}
                  </span>
                  {page.latest_scan && <StatusBadge status={page.latest_scan.status} />}
                </div>
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  )
}

export default WebsiteDetails
