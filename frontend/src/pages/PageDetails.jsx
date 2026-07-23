import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import apiClient from '../api/client'
import MetricCard from '../components/MetricCard'
import TrendChart from '../components/TrendChart'
import ScanHistoryTable from '../components/ScanHistoryTable'
import RawReportViewer from '../components/RawReportViewer'
import StatusBadge from '../components/StatusBadge'
import PageErrorLog from '../components/PageErrorLog'

function PageDetails() {
  const { pageId } = useParams()
  const [details, setDetails] = useState(null)
  const [error, setError] = useState(null)

  useEffect(() => {
    setError(null)
    apiClient
      .get(`/pages/${pageId}`)
      .then((res) => setDetails(res.data))
      .catch(() => setError('Could not load this page.'))
  }, [pageId])

  if (error) {
    return <p className="text-sm text-red-600">{error}</p>
  }

  if (!details) {
    return <p className="text-sm text-gray-400">Loading…</p>
  }

  const { page, latest_scan: latestScan, scan_history: scanHistory,
    performance_history: performanceHistory, raw_report: rawReport, page_errors: pageErrors } = details
  const latestResult = latestScan?.scan_result

  const chartData = performanceHistory.map((row) => ({ scanned_at: row.scanned_at, value: row.performance }))

  return (
    <div className="space-y-6">
      <div className="rounded-lg border border-gray-200 bg-white p-4">
        <div className="flex items-start justify-between">
          <div>
            <Link to={`/websites/${page.website_id}`} className="text-xs text-indigo-600 hover:underline">
              {page.website?.name}
            </Link>
            <h2 className="text-lg font-semibold text-gray-900">{page.url}</h2>
            <p className="text-xs capitalize text-gray-400">{page.page_type}</p>
          </div>
          <StatusBadge status={page.enabled ? 'enabled' : 'disabled'} />
        </div>
      </div>

      <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <MetricCard label="Performance" value={latestResult?.performance ?? '—'} />
        <MetricCard label="LCP (ms)" value={latestResult?.lcp ?? '—'} />
        <MetricCard label="CLS" value={latestResult?.cls ?? '—'} />
        <MetricCard label="TBT (ms)" value={latestResult?.tbt ?? '—'} />
      </div>

      <TrendChart title="Performance Trend" data={chartData} metric="performance" />

      <div className="rounded-lg border border-gray-200 bg-white p-4">
        <h3 className="mb-2 text-sm font-medium text-gray-900">Scan History</h3>
        <ScanHistoryTable scans={scanHistory} />
      </div>

      <div className="rounded-lg border border-gray-200 bg-white p-4">
        <h3 className="mb-2 text-sm font-medium text-gray-900">Raw Lighthouse Report</h3>
        <RawReportViewer report={rawReport} />
      </div>

      <div className="rounded-lg border border-gray-200 bg-white p-4">
        <h3 className="mb-2 text-sm font-medium text-gray-900">JS Console Errors</h3>
        <PageErrorLog errors={pageErrors} />
      </div>
    </div>
  )
}

export default PageDetails
