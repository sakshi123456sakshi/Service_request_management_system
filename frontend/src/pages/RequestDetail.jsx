import { useEffect, useState } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { requestApi, technicianApi } from '../api/endpoints'
import { useAuth } from '../context/AuthContext'
import StatusBadge from '../components/StatusBadge'
import PriorityBadge from '../components/PriorityBadge'
import HistoryTimeline from '../components/HistoryTimeline'

const NEXT_STATUS = {
  admin: { New: null, Assigned: 'In Progress', 'In Progress': 'Resolved', Resolved: 'Closed' },
  technician: { Assigned: 'In Progress', 'In Progress': 'Resolved' },
}

export default function RequestDetail() {
  const { id } = useParams()
  const { user } = useAuth()
  const navigate = useNavigate()

  const [request, setRequest] = useState(null)
  const [technicians, setTechnicians] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [actionMsg, setActionMsg] = useState('')
  const [remarkText, setRemarkText] = useState('')
  const [selectedTech, setSelectedTech] = useState('')
  const [selectedPriority, setSelectedPriority] = useState('')
  const [busy, setBusy] = useState(false);

  const load = async () => {
    setLoading(true)
    try {
      const res = await requestApi.detail(id)
      setRequest(res.data)
      setSelectedPriority(res.data.priority)
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    load()
    if (user?.role === 'admin') {
      technicianApi.list().then((res) => setTechnicians(res.data)).catch(() => {})
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id])

  const runAction = async (fn, successMsg) => {
    setBusy(true)
    setError('')
    setActionMsg('')
    try {
      await fn()
      setActionMsg(successMsg)
      await load()
    } catch (err) {
      setError(err.message)
    } finally {
      setBusy(false)
    }
  }

  if (loading) return <div className="loading">Loading...</div>
  if (!request) return <div className="container"><div className="error-box">{error || 'Not found'}</div></div>

  const nextStatus = NEXT_STATUS[user.role]?.[request.status]

  return (
    <div className="container">
      <div className="page-header">
        <div>
          <h1>{request.request_code} — {request.subject}</h1>
          <div style={{ display: 'flex', gap: 8, marginTop: 6 }}>
            <StatusBadge status={request.status} />
            <PriorityBadge priority={request.priority} />
          </div>
        </div>
        <button className="btn btn-secondary" onClick={() => navigate(-1)}>← Back</button>
      </div>

      {error && <div className="error-box">{error}</div>}
      {actionMsg && <div className="success-box">{actionMsg}</div>}

      <div className="detail-grid">
        <div>
          <div className="card detail-section">
            <h3>Description</h3>
            <p style={{ whiteSpace: 'pre-wrap' }}>{request.description}</p>
            {request.attachment_path && (
              <p>
                <a href={`${import.meta.env.VITE_API_URL}/${request.attachment_path}`} target="_blank" rel="noreferrer">
                  📎 View Attachment
                </a>
              </p>
            )}
          </div>

          <div className="card detail-section">
            <h3>Status History</h3>
            <HistoryTimeline history={request.history} />
          </div>

          <div className="card detail-section">
            <h3>Remarks</h3>
            {request.remarks.length === 0 && <p style={{ color: '#6b7280', fontSize: 13 }}>No remarks yet.</p>}
            {request.remarks.map((r) => (
              <div key={r.id} className="remark-item">
                <div>{r.remark}</div>
                <div className="remark-meta">{r.author_name} ({r.role}) · {new Date(r.created_at).toLocaleString()}</div>
              </div>
            ))}

            {request.status !== 'Closed' && (
              <div style={{ marginTop: 14 }}>
                <div className="field">
                  <textarea rows={3} placeholder="Add a remark..." value={remarkText} onChange={(e) => setRemarkText(e.target.value)} />
                </div>
                <button
                  className="btn btn-primary btn-sm"
                  disabled={busy || !remarkText.trim()}
                  onClick={() =>
                    runAction(async () => {
                      await requestApi.remark(request.id, remarkText)
                      setRemarkText('')
                    }, 'Remark added.')
                  }
                >
                  Add Remark
                </button>
              </div>
            )}
          </div>
        </div>

        <div>
          <div className="card detail-section">
            <h3>Request Info</h3>
            <div className="kv"><span className="k">Category</span><span>{request.category_name}</span></div>
            <div className="kv"><span className="k">Raised By</span><span>{request.raised_by_name}</span></div>
            <div className="kv"><span className="k">Email</span><span>{request.raised_by_email}</span></div>
            <div className="kv"><span className="k">Date Raised</span><span>{new Date(request.created_at).toLocaleString()}</span></div>
            <div className="kv"><span className="k">Technician</span><span>{request.technician_name || 'Unassigned'}</span></div>
          </div>

          {user.role === 'admin' && request.status !== 'Closed' && (
            <div className="card detail-section">
              <h3>Admin Actions</h3>

              <div className="field">
                <label>Assign / Reassign Technician</label>
                <select value={selectedTech} onChange={(e) => setSelectedTech(e.target.value)}>
                  <option value="">Select technician</option>
                  {technicians.map((t) => (
                    <option key={t.id} value={t.id}>{t.name}</option>
                  ))}
                </select>
                <button
                  className="btn btn-primary btn-sm"
                  style={{ marginTop: 8 }}
                  disabled={busy || !selectedTech}
                  onClick={() => runAction(() => requestApi.assign(request.id, selectedTech), 'Technician assigned.')}
                >
                  Assign
                </button>
              </div>

              <div className="field">
                <label>Change Priority</label>
                <select value={selectedPriority} onChange={(e) => setSelectedPriority(e.target.value)}>
                  <option>Low</option><option>Medium</option><option>High</option><option>Critical</option>
                </select>
                <button
                  className="btn btn-secondary btn-sm"
                  style={{ marginTop: 8 }}
                  disabled={busy || selectedPriority === request.priority}
                  onClick={() => runAction(() => requestApi.priority(request.id, selectedPriority), 'Priority updated.')}
                >
                  Update Priority
                </button>
              </div>

              {nextStatus && (
                <button
                  className="btn btn-primary"
                  disabled={busy}
                  onClick={() => runAction(() => requestApi.status(request.id, nextStatus), `Status moved to ${nextStatus}.`)}
                >
                  Move to "{nextStatus}"
                </button>
              )}
            </div>
          )}

          {user.role === 'technician' && (
            <div className="card detail-section">
              <h3>Technician Actions</h3>
              {nextStatus ? (
                <button
                  className="btn btn-primary btn-block"
                  disabled={busy}
                  onClick={() => runAction(() => requestApi.status(request.id, nextStatus, remarkText), `Marked as ${nextStatus}.`)}
                >
                  {request.status === 'Assigned' ? 'Accept & Start Work' : `Mark as ${nextStatus}`}
                </button>
              ) : (
                <p style={{ color: '#6b7280', fontSize: 13 }}>No further action available.</p>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
