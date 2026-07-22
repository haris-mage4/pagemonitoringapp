import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import apiClient from '../api/client'
import WebsiteCard from '../components/WebsiteCard'
import TrendChart from '../components/TrendChart'
import StatusBadge from '../components/StatusBadge'

function WebsiteDetails() {
  const { websiteId } = useParams()
  const [details, setDetails] = useState(null)

  useEffect(() => {
    apiClient.get(`/websites/${websiteId}`).then((res) => setDetails(res.data))
  }, [websiteId])

  if (!details) {
    return <p className="text-sm text-gray-400">Loading…</p>
  }

  const { website, pages, latest_scan: latestScan, current_score: currentScore,
    previous_score: previousScore, performance_history: performanceHistory,
    next_scheduled_scan: nextScheduledScan } = details

  const chartData = performanceHistory.map((row) => ({ scanned_at: row.scanned_at, value: row.performance }))

  return (
    <div className="space-y-6">
      <WebsiteCard
        website={website}
        currentScore={currentScore}
        previousScore={previousScore}
        nextScheduledScan={nextScheduledScan}
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
        <h3 className="mb-2 text-sm font-medium text-gray-900">Pages</h3>
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
