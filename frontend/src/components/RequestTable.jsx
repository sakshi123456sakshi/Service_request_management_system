import { useNavigate } from 'react-router-dom'
import StatusBadge from './StatusBadge'
import PriorityBadge from './PriorityBadge'

export default function RequestTable({ requests, showRequester = false, showTechnician = false }) {
  const navigate = useNavigate()

  if (!requests || requests.length === 0) {
    return <div className="table-wrap"><div className="empty-state">No service requests found.</div></div>
  }

  return (
    <div className="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Request ID</th>
            <th>Subject</th>
            <th>Category</th>
            {showRequester && <th>Raised By</th>}
            {showTechnician && <th>Technician</th>}
            <th>Priority</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          {requests.map((r) => (
            <tr key={r.id} style={{ cursor: 'pointer' }} onClick={() => navigate(`/requests/${r.id}`)}>
              <td>{r.request_code}</td>
              <td>{r.subject}</td>
              <td>{r.category_name}</td>
              {showRequester && <td>{r.raised_by_name}</td>}
              {showTechnician && <td>{r.technician_name || '—'}</td>}
              <td><PriorityBadge priority={r.priority} /></td>
              <td><StatusBadge status={r.status} /></td>
              <td>{new Date(r.created_at).toLocaleDateString()}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
