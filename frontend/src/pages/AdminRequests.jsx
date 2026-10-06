import { useEffect, useState } from 'react'
import { categoryApi, requestApi } from '../api/endpoints'
import RequestTable from '../components/RequestTable'

export default function AdminRequests() {
  const [requests, setRequests] = useState([])
  const [categories, setCategories] = useState([])
  const [loading, setLoading] = useState(true)
  const [filters, setFilters] = useState({ q: '', status: '', priority: '', category_id: '' })

  useEffect(() => {
    categoryApi.list().then((res) => setCategories(res.data)).catch(() => {})
    load()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const load = async (params = {}) => {
    setLoading(true)
    try {
      const res = await requestApi.list(params)
      setRequests(res.data)
    } finally {
      setLoading(false)
    }
  }

  const applyFilters = (e) => {
    e.preventDefault()
    const params = {}
    Object.entries(filters).forEach(([k, v]) => { if (v) params[k] = v })
    load(params)
  }

  return (
    <div className="container">
      <div className="page-header">
        <h1>All Service Requests</h1>
      </div>

      <form className="filters" onSubmit={applyFilters}>
        <input
          placeholder="Search by subject / ID..."
          value={filters.q}
          onChange={(e) => setFilters({ ...filters, q: e.target.value })}
        />
        <select value={filters.status} onChange={(e) => setFilters({ ...filters, status: e.target.value })}>
          <option value="">All Statuses</option>
          <option>New</option><option>Assigned</option><option>In Progress</option><option>Resolved</option><option>Closed</option>
        </select>
        <select value={filters.priority} onChange={(e) => setFilters({ ...filters, priority: e.target.value })}>
          <option value="">All Priorities</option>
          <option>Low</option><option>Medium</option><option>High</option><option>Critical</option>
        </select>
        <select value={filters.category_id} onChange={(e) => setFilters({ ...filters, category_id: e.target.value })}>
          <option value="">All Categories</option>
          {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
        </select>
        <button className="btn btn-secondary" type="submit">Filter</button>
      </form>

      {loading ? <div className="loading">Loading...</div> : <RequestTable requests={requests} showRequester showTechnician />}
    </div>
  )
}
