import { Routes, Route, Navigate } from 'react-router-dom'
import { useAuth } from './context/AuthContext'
import Navbar from './components/Navbar'
import ProtectedRoute from './components/ProtectedRoute'

import Login from './pages/Login'
import Register from './pages/Register'
import UserDashboard from './pages/UserDashboard'
import NewRequest from './pages/NewRequest'
import RequestDetail from './pages/RequestDetail'
import AdminDashboard from './pages/AdminDashboard'
import AdminRequests from './pages/AdminRequests'
import TechnicianDashboard from './pages/TechnicianDashboard'

function Home() {
  const { user } = useAuth()
  if (user?.role === 'admin') return <AdminDashboard />
  if (user?.role === 'technician') return <TechnicianDashboard />
  return <UserDashboard />
}

export default function App() {
  const { user, loading } = useAuth()

  if (loading) return <div className="loading">Loading...</div>

  return (
    <>
      <Navbar />
      <Routes>
        <Route path="/login" element={user ? <Navigate to="/" /> : <Login />} />
        <Route path="/register" element={user ? <Navigate to="/" /> : <Register />} />

        <Route path="/" element={<ProtectedRoute><Home /></ProtectedRoute>} />

        <Route path="/new-request" element={
          <ProtectedRoute roles={['user']}><NewRequest /></ProtectedRoute>
        } />

        <Route path="/requests" element={
          <ProtectedRoute roles={['admin']}><AdminRequests /></ProtectedRoute>
        } />

        <Route path="/requests/:id" element={
          <ProtectedRoute><RequestDetail /></ProtectedRoute>
        } />

        <Route path="*" element={<Navigate to="/" />} />
      </Routes>
    </>
  )
}
