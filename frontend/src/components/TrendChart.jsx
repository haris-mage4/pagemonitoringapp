import {
  CartesianGrid,
  Line,
  LineChart,
  ReferenceArea,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts'
import { getThresholds, statusColor, STATUS_COLORS, STATUS_LABELS } from '../lib/thresholds'

function formatTick(value) {
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString()
}

function ColoredDot(metric) {
  return function Dot(props) {
    const { cx, cy, payload } = props
    if (cx == null || cy == null) return null
    return <circle cx={cx} cy={cy} r={5} fill={statusColor(metric, payload.value)} stroke="#fff" strokeWidth={1.5} />
  }
}

function thresholdBands(metric, maxValue) {
  const t = getThresholds(metric)
  if (!t) return null

  const top = t.max ?? Math.max(maxValue * 1.15, t.poor * 1.5, 1)

  return t.higherIsBetter
    ? [
        { key: 'poor', y1: 0, y2: t.poor, color: STATUS_COLORS.poor },
        { key: 'needs_improvement', y1: t.poor, y2: t.good, color: STATUS_COLORS.needs_improvement },
        { key: 'good', y1: t.good, y2: top, color: STATUS_COLORS.good },
      ]
    : [
        { key: 'good', y1: 0, y2: t.good, color: STATUS_COLORS.good },
        { key: 'needs_improvement', y1: t.good, y2: t.poor, color: STATUS_COLORS.needs_improvement },
        { key: 'poor', y1: t.poor, y2: top, color: STATUS_COLORS.poor },
      ]
}

function TrendChart({ title, data, metric }) {
  const maxValue = data.reduce((max, row) => Math.max(max, row.value ?? 0), 0)
  const bands = thresholdBands(metric, maxValue)

  return (
    <div className="rounded-lg border border-gray-200 bg-white p-4">
      <div className="mb-2 flex items-center justify-between">
        <h3 className="text-sm font-medium text-gray-900">{title}</h3>
      </div>
      <div className="h-48">
        {data.length === 0 ? (
          <div className="flex h-full items-center justify-center text-sm text-gray-400">No data yet</div>
        ) : (
          <ResponsiveContainer width="100%" height="100%">
            <LineChart data={data} margin={{ top: 8, right: 8, left: 0, bottom: 0 }}>
              <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
              {bands?.map((band) => (
                <ReferenceArea
                  key={band.key}
                  y1={band.y1}
                  y2={band.y2}
                  fill={band.color}
                  fillOpacity={0.06}
                  ifOverflow="extendDomain"
                />
              ))}
              <XAxis dataKey="scanned_at" tickFormatter={formatTick} tick={{ fontSize: 11 }} />
              <YAxis tick={{ fontSize: 11 }} width={40} />
              <Tooltip labelFormatter={formatTick} />
              <Line
                type="monotone"
                dataKey="value"
                stroke="#94a3b8"
                strokeWidth={2}
                dot={ColoredDot(metric)}
                activeDot={{ r: 7 }}
                isAnimationActive={false}
              />
            </LineChart>
          </ResponsiveContainer>
        )}
      </div>
      {metric && getThresholds(metric) && (
        <div className="mt-2 flex items-center gap-4 text-xs text-gray-500">
          {Object.entries(STATUS_LABELS).map(([key, label]) => (
            <span key={key} className="flex items-center gap-1">
              <span className="inline-block h-2 w-2 rounded-full" style={{ backgroundColor: STATUS_COLORS[key] }} />
              {label}
            </span>
          ))}
        </div>
      )}
    </div>
  )
}

export default TrendChart
