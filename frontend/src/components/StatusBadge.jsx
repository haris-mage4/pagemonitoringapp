const STYLES = {
  pending: 'bg-gray-100 text-gray-700',
  running: 'bg-blue-100 text-blue-700',
  completed: 'bg-green-100 text-green-700',
  failed: 'bg-red-100 text-red-700',
  enabled: 'bg-green-100 text-green-700',
  disabled: 'bg-gray-100 text-gray-700',
}

function StatusBadge({ status }) {
  const style = STYLES[status] ?? 'bg-gray-100 text-gray-700'

  return (
    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${style}`}>
      {status}
    </span>
  )
}

export default StatusBadge
