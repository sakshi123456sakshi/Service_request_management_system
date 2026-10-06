import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { requestApi } from '../api/endpoints'
import RequestTable from '../components/RequestTable'

export default function UserDashboard() {
  const [requests, setRequests] = useState([])
  const [loading, setLoading] = useState(true)
  const [filters, setFilters] = useState({ q: '', status: '', priority: '' })

  const load = async (params = {}) => {
    setLoading(true)
    try {
      const res = await requestApi.list(params)
      setRequests(res.data)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { load() }, [])

  const applyFilters = (e) => {
    e.preventDefault()
    const params = {}
    if (filters.q) params.q = filters.q
    if (filters.status) params.status = filters.status
    if (filters.priority) params.priority = filters.priority
    load(params)
  }

  return (
    <div className="container">
      <div className="page-header">
        <h1>My Service Requests</h1>
        <Link to="/new-request" className="btn btn-primary">+ New Request</Link>
      </div>

      <form className="filters" onSubmit={applyFilters}>
        <input
          placeholder="Search by subject / ID..."
          value={filters.q}
          onChange={(e) => setFilters({ ...filters, q: e.target.value })}
        />
        <select value={filters.status} onChange={(e) => setFilters({ ...filters, status: e.target.value })}>
          <option value="">All Statuses</option>
          <option>New</option>
          <option>Assigned</option>
          <option>In Progress</option>
          <option>Resolved</option>
          <option>Closed</option>
        </select>
        <select value={filters.priority} onChange={(e) => setFilters({ ...filters, priority: e.target.value })}>
          <option value="">All Priorities</option>
          <option>Low</option>
          <option>Medium</option>
          <option>High</option>
          <option>Critical</option>
        </select>
        <button className="btn btn-secondary" type="submit">Filter</button>
      </form>

      {loading ? <div className="loading">Loading...</div> : <RequestTable requests={requests} />}
    </div>
  )
}
