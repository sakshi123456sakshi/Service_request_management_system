export default function StatusBadge({ status }) {
  const cls = 'status-' + status.replace(' ', '')
  return <span className={`badge ${cls}`}>{status}</span>
}
