import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { authApi } from '../api/endpoints'

export default function Register() {
  const navigate = useNavigate()
  const [form, setForm] = useState({ name: '', email: '', phone: '', password: '', confirm: '' })
  const [error, setError] = useState('')
  const [success, setSuccess] = useState('')
  const [loading, setLoading] = useState(false)

  const update = (k) => (e) => setForm({ ...form, [k]: e.target.value })

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setSuccess('')

    if (form.password !== form.confirm) {
      setError('Passwords do not match.')
      return
    }

    setLoading(true)
    try {
      await authApi.register({ name: form.name, email: form.email, phone: form.phone, password: form.password })
      setSuccess('Registration successful! Redirecting to login...')
      setTimeout(() => navigate('/login'), 1200)
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="auth-wrap">
      <div className="auth-card" style={{ width: 420 }}>
        <h1>Create an account</h1>
        <p className="subtitle">Register to raise and track service requests</p>

        {error && <div className="error-box">{error}</div>}
        {success && <div className="success-box">{success}</div>}

        <form onSubmit={handleSubmit}>
          <div className="field">
            <label>Full Name</label>
            <input required value={form.name} onChange={update('name')} placeholder="Sakshi Sharma" />
          </div>
          <div className="field">
            <label>Email</label>
            <input type="email" required value={form.email} onChange={update('email')} placeholder="you@example.com" />
          </div>
          <div className="field">
            <label>Phone (optional)</label>
            <input value={form.phone} onChange={update('phone')} placeholder="9876543210" />
          </div>
          <div className="field-row">
            <div className="field">
              <label>Password</label>
              <input type="password" required value={form.password} onChange={update('password')} placeholder="Min 8 characters" />
            </div>
            <div className="field">
              <label>Confirm Password</label>
              <input type="password" required value={form.confirm} onChange={update('confirm')} />
            </div>
          </div>
          <button className="btn btn-primary btn-block" disabled={loading}>
            {loading ? 'Creating account...' : 'Register'}
          </button>
        </form>

        <p style={{ marginTop: 16, fontSize: 13, textAlign: 'center' }}>
          Already have an account? <Link to="/login">Sign in</Link>
        </p>
      </div>
    </div>
  )
}
