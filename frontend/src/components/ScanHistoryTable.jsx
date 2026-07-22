import StatusBadge from './StatusBadge'

function ScanHistoryTable({ scans }) {
  if (scans.length === 0) {
    return <p className="text-sm text-gray-400">No scans yet.</p>
  }

  return (
    <table className="w-full text-left text-sm">
      <thead>
        <tr className="border-b border-gray-200 text-gray-500">
          <th className="py-2 font-medium">Finished</th>
          <th className="py-2 font-medium">Trigger</th>
          <th className="py-2 font-medium">Status</th>
          <th className="py-2 font-medium">Performance</th>
          <th className="py-2 font-medium">LCP</th>
          <th className="py-2 font-medium">CLS</th>
          <th className="py-2 font-medium">TBT</th>
        </tr>
      </thead>
      <tbody className="divide-y divide-gray-100">
        {scans.map((scan) => (
          <tr key={scan.id}>
            <td className="py-2 text-gray-600">
              {scan.finished_at ? new Date(scan.finished_at).toLocaleString() : '—'}
            </td>
            <td className="py-2 capitalize text-gray-600">{scan.trigger}</td>
            <td className="py-2">
              <StatusBadge status={scan.status} />
            </td>
            <td className="py-2 text-gray-600">{scan.scan_result?.performance ?? '—'}</td>
            <td className="py-2 text-gray-600">{scan.scan_result?.lcp ?? '—'}</td>
            <td className="py-2 text-gray-600">{scan.scan_result?.cls ?? '—'}</td>
            <td className="py-2 text-gray-600">{scan.scan_result?.tbt ?? '—'}</td>
          </tr>
        ))}
      </tbody>
    </table>
  )
}

export default ScanHistoryTable
