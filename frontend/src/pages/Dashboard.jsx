import { useCallback, useEffect, useState } from 'react'
import apiClient from '../api/client'
import MetricCard from '../components/MetricCard'
import TrendChart from '../components/TrendChart'
import RecentActivity from '../components/RecentActivity'

const TREND_METRICS = [
  { key: 'performance', title: 'Performance' },
  { key: 'lcp', title: 'LCP' },
  { key: 'cls', title: 'CLS' },
  { key: 'tbt', title: 'TBT' },
]

function Dashboard() {
  const [summary, setSummary] = useState(null)
  const [ranges, setRanges] = useState({ performance: '7d', lcp: '7d', cls: '7d', tbt: '7d' })
  const [trends, setTrends] = useState({ performance: [], lcp: [], cls: [], tbt: [] })

  useEffect(() => {
    apiClient.get('/dashboard/summary').then((res) => setSummary(res.data))
  }, [])

  const fetchTrend = useCallback((metric, range) => {
    apiClient
      .get(`/dashboard/trend/${metric}`, { params: { range } })
      .then((res) => setTrends((prev) => ({ ...prev, [metric]: res.data })))
  }, [])

  useEffect(() => {
    TREND_METRICS.forEach(({ key }) => fetchTrend(key, ranges[key]))
  }, [ranges, fetchTrend])

  const setRange = (metric, range) => setRanges((prev) => ({ ...prev, [metric]: range }))

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
          <TrendChart
            key={key}
            title={title}
            data={trends[key]}
            range={ranges[key]}
            onRangeChange={(range) => setRange(key, range)}
          />
        ))}
      </div>

      <div className="rounded-lg border border-gray-200 bg-white p-4">
        <h3 className="mb-2 text-sm font-medium text-gray-900">Recent Activity</h3>
        <RecentActivity scans={summary?.recent_activity ?? []} />
      </div>
    </div>
  )
}

export default Dashboard
