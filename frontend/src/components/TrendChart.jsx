import { CartesianGrid, Line, LineChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts'

const RANGES = [
  { value: '24h', label: '24h' },
  { value: '7d', label: '7d' },
  { value: '30d', label: '30d' },
]

function formatTick(value) {
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString()
}

function TrendChart({ title, data, range, onRangeChange }) {
  return (
    <div className="rounded-lg border border-gray-200 bg-white p-4">
      <div className="mb-2 flex items-center justify-between">
        <h3 className="text-sm font-medium text-gray-900">{title}</h3>
        {onRangeChange && (
          <div className="flex gap-1">
            {RANGES.map((r) => (
              <button
                key={r.value}
                type="button"
                onClick={() => onRangeChange(r.value)}
                className={`rounded px-2 py-0.5 text-xs font-medium ${
                  range === r.value ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-100'
                }`}
              >
                {r.label}
              </button>
            ))}
          </div>
        )}
      </div>
      <div className="h-48">
        {data.length === 0 ? (
          <div className="flex h-full items-center justify-center text-sm text-gray-400">No data yet</div>
        ) : (
          <ResponsiveContainer width="100%" height="100%">
            <LineChart data={data}>
              <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
              <XAxis dataKey="scanned_at" tickFormatter={formatTick} tick={{ fontSize: 11 }} />
              <YAxis tick={{ fontSize: 11 }} />
              <Tooltip labelFormatter={formatTick} />
              <Line type="monotone" dataKey="value" stroke="#4f46e5" dot={false} strokeWidth={2} />
            </LineChart>
          </ResponsiveContainer>
        )}
      </div>
    </div>
  )
}

export default TrendChart
