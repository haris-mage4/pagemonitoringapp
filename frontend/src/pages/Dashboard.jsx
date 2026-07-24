import { useEffect, useState } from 'react'
import apiClient from '../api/client'
import MetricCard from '../components/MetricCard'
import TrendChart from '../components/TrendChart'
import RecentActivity from '../components/RecentActivity'
import RecentPageErrors from '../components/RecentPageErrors'
import DashboardPageScanList from '../components/DashboardPageScanList'

const TREND_METRICS = [
  { key: 'performance', title: 'Performance (Last 5 Scans)' },
  { key: 'lcp', title: 'LCP (Last 5 Scans)' },
  { key: 'cls', title: 'CLS (Last 5 Scans)' },
  { key: 'tbt', title: 'TBT (Last 5 Scans)' },
]

function Dashboard() {
  const [summary, setSummary] = useState(null)
  const [error, setError] = useState(null)
  const [trends, setTrends] = useState({ performance: [], lcp: [], cls: [], tbt: [] })

  useEffect(() => {
    apiClient
      .get('/dashboard/summary')
      .then((res) => setSummary(res.data))
      .catch(() => setError('Could not load dashboard data.'))
  }, [])

  useEffect(() => {
    TREND_METRICS.forEach(({ key }) => {
      apiClient
        .get(`/dashboard/trend/${key}`)
        .then((res) => setTrends((prev) => ({ ...prev, [key]: res.data })))
    })
  }, [])

  if (error) {
    return <p className="text-sm text-red-600">{error}</p>
  }

  if (!summary) {
    return <p className="text-sm text-gray-400">Loading…</p>
  }

  return (
    <div className="space-y-6">
      <h2 className="text-lg font-medium text-gray-900">Dashboard</h2>

      <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <MetricCard label="Total Websites" value={summary?.total_websites ?? '—'} />
        <MetricCard
          label="Last Scan"
          value={summary?.last_scan ? new Date(summary.last_scan.finished_at).toLocaleString() : '—'}
        />
        <MetricCard label="Failed Scans" value={summary?.failed_scans ?? '—'} />
        <MetricCard label="Average Performance" value={summary?.average_performance ?? '—'} />
      </div>

      <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {TREND_METRICS.map(({ key, title }) => (
          <TrendChart key={key} title={title} data={trends[key]} metric={key} />
        ))}
      </div>

      <div className="rounded-lg border border-gray-200 bg-white p-4">
        <h3 className="mb-2 text-sm font-medium text-gray-900">Recent Activity</h3>
        <RecentActivity scans={summary?.recent_activity ?? []} />
      </div>

      <div className="rounded-lg border border-gray-200 bg-white p-4">
        <h3 className="mb-2 text-sm font-medium text-gray-900">Pages — Scan List</h3>
        <DashboardPageScanList pages={summary?.pages ?? []} />
      </div>

      <div className="rounded-lg border border-gray-200 bg-white p-4">
        <h3 className="mb-2 text-sm font-medium text-gray-900">Recent JS Console Errors</h3>
        <RecentPageErrors errors={summary?.recent_page_errors ?? []} />
      </div>
    </div>
  )
}

export default Dashboard
