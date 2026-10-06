import StatusBadge from './StatusBadge'

export default function HistoryTimeline({ history }) {
  if (!history || history.length === 0) {
    return <p style={{ color: '#6b7280', fontSize: 13 }}>No history yet.</p>
  }
  return (
    <ul className="timeline">
      {history.map((h) => (
        <li key={h.id}>
          <StatusBadge status={h.status} />
          <div style={{ marginTop: 4 }}>{h.remarks}</div>
          <div className="who">
            {h.updated_by_name} ({h.updated_by_role})
          </div>
          <div className="ts">{new Date(h.created_at).toLocaleString()}</div>
        </li>
      ))}
    </ul>
  )
}
