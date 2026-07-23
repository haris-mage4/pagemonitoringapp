import { useMemo, useState } from 'react'

function PageErrorLog({ errors }) {
  const [search, setSearch] = useState('')
  const [since, setSince] = useState('')

  const filtered = useMemo(() => {
    return errors.filter((e) => {
      if (search && !e.message.toLowerCase().includes(search.toLowerCase())) return false
      if (since && new Date(e.last_seen_at) < new Date(since)) return false
      return true
    })
  }, [errors, search, since])

  if (errors.length === 0) {
    return <p className="text-sm text-gray-400">No JS console errors detected.</p>
  }

  return (
    <div>
      <div className="mb-3 flex flex-wrap gap-3">
        <input
          type="text"
          placeholder="Search messages…"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="flex-1 min-w-[200px] rounded-md border border-gray-300 px-3 py-1.5 text-sm"
        />
        <input
          type="date"
          value={since}
          onChange={(e) => setSince(e.target.value)}
          className="rounded-md border border-gray-300 px-3 py-1.5 text-sm"
        />
      </div>

      {filtered.length === 0 ? (
        <p className="text-sm text-gray-400">No errors match this filter.</p>
      ) : (
        <ul className="divide-y divide-gray-100">
          {filtered.map((e) => (
            <li key={e.id} className="py-2">
              <div className="flex items-start justify-between gap-3">
                <p className="text-sm font-medium text-gray-900 break-all">{e.message}</p>
                <span className="shrink-0 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                  ×{e.occurrence_count}
                </span>
              </div>
              {e.source && <p className="text-xs text-gray-400 break-all">{e.source}</p>}
              <p className="text-xs text-gray-400">
                First seen {new Date(e.first_seen_at).toLocaleString()} · Last seen{' '}
                {new Date(e.last_seen_at).toLocaleString()}
              </p>
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}

export default PageErrorLog
