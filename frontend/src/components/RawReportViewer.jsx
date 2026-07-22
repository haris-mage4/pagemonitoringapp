import { useState } from 'react'

function RawReportViewer({ report }) {
  const [expanded, setExpanded] = useState(false)

  if (!report) {
    return <p className="text-sm text-gray-400">No raw report available.</p>
  }

  return (
    <div>
      <button
        type="button"
        onClick={() => setExpanded((prev) => !prev)}
        className="text-sm font-medium text-indigo-600 hover:underline"
      >
        {expanded ? 'Hide' : 'Show'} raw Lighthouse report
      </button>
      {expanded && (
        <pre className="mt-2 max-h-96 overflow-auto rounded bg-gray-900 p-3 text-xs text-gray-100">
          {JSON.stringify(report, null, 2)}
        </pre>
      )}
    </div>
  )
}

export default RawReportViewer
