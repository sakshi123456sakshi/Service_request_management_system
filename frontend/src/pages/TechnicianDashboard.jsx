import { useEffect, useState } from 'react'
import { dashboardApi, requestApi } from '../api/endpoints'
import DashboardCards from '../components/DashboardCards'
import RequestTable from '../components/RequestTable'

export default function TechnicianDashboard() {
  const [summary, setSummary] = useState(null)
  const [requests, setRequests] = useState([])
  const [loading, setLoading] = useState(true)
  const [status, setStatus] = useState('')

  const load = async (params = {}) => {
    setLoading(true)
    try {
      const res = await requestApi.list(params)
      setRequests(res.data)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    dashboardApi.summary().then((res) => setSummary(res.data)).catch(() => {})
    load()
  }, [])

  const applyFilter = (e) => {
    e.preventDefault()
    load(status ? { status } : {})
  }

  return (
    <div className="container">
      <div className="page-header">
        <h1>My Assigned Requests</h1>
      </div>

      <DashboardCards summary={summary} />

      <form className="filters" onSubmit={applyFilter}>
        <select value={status} onChange={(e) => setStatus(e.target.value)}>
          <option value="">All Statuses</option>
          <option>Assigned</option>
          <option>In Progress</option>
          <option>Resolved</option>
          <option>Closed</option>
        </select>
        <button className="btn btn-secondary" type="submit">Filter</button>
      </form>

      {loading ? <div className="loading">Loading...</div> : <RequestTable requests={requests} showRequester />}
    </div>
  )
}
