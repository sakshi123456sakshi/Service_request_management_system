import { useEffect, useState } from 'react'
import { dashboardApi, requestApi } from '../api/endpoints'
import DashboardCards from '../components/DashboardCards'
import RequestTable from '../components/RequestTable'

export default function AdminDashboard() {
  const [summary, setSummary] = useState(null)
  const [recent, setRecent] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    Promise.all([dashboardApi.summary(), requestApi.list()])
      .then(([s, r]) => {
        setSummary(s.data)
        setRecent(r.data.slice(0, 8))
      })
      .finally(() => setLoading(false))
  }, [])

  if (loading) return <div className="loading">Loading...</div>

  return (
    <div className="container">
      <div className="page-header">
        <h1>Admin Dashboard</h1>
      </div>

      <DashboardCards summary={summary} />

      <div className="page-header">
        <h1 style={{ fontSize: 18 }}>Recent Requests</h1>
      </div>
      <RequestTable requests={recent} showRequester showTechnician />
    </div>
  )
}
