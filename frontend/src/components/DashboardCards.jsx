export default function DashboardCards({ summary }) {
  if (!summary) return null

  const cards = [
    { label: 'Total Requests', value: summary.total_requests, accent: 'total' },
    { label: 'New', value: summary.new, accent: 'new' },
    { label: 'Assigned', value: summary.assigned, accent: 'assigned' },
    { label: 'In Progress', value: summary.in_progress, accent: 'progress' },
    { label: 'Resolved', value: summary.resolved, accent: 'resolved' },
    { label: 'Closed', value: summary.closed, accent: 'closed' },
    { label: 'Critical Priority', value: summary.critical, accent: 'critical' },
  ]

  return (
    <>
      <div className="stat-grid">
        {cards.map((c) => (
          <div key={c.label} className={`stat-card accent-${c.accent}`}>
            <div className="stat-value">{c.value}</div>
            <div className="stat-label">{c.label}</div>
          </div>
        ))}
      </div>

      {summary.by_category?.length > 0 && (
        <div className="card" style={{ marginBottom: 24 }}>
          <h3 style={{ marginTop: 0, fontSize: 14, color: '#6b7280', textTransform: 'uppercase' }}>
            Category-wise Summary
          </h3>
          <div className="category-summary">
            {summary.by_category.map((c) => (
              <span key={c.category} className="category-chip">
                {c.category}: {c.total}
              </span>
            ))}
          </div>
        </div>
      )}
    </>
  )
}
