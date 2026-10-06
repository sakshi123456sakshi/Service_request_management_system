import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { categoryApi, requestApi } from '../api/endpoints'

export default function NewRequest() {
  const navigate = useNavigate()
  const [categories, setCategories] = useState([])
  const [form, setForm] = useState({ subject: '', category_id: '', description: '', priority: 'Low' })
  const [file, setFile] = useState(null)
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    categoryApi.list().then((res) => setCategories(res.data)).catch(() => {})
  }, [])

  const update = (k) => (e) => setForm({ ...form, [k]: e.target.value })

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      const fd = new FormData()
      Object.entries(form).forEach(([k, v]) => fd.append(k, v))
      if (file) fd.append('attachment', file)

      const res = await requestApi.create(fd)
      navigate(`/requests/${res.data.id}`)
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="container" style={{ maxWidth: 640 }}>
      <div className="page-header">
        <h1>Raise a New Service Request</h1>
      </div>

      <div className="card">
        {error && <div className="error-box">{error}</div>}

        <form onSubmit={handleSubmit}>
          <div className="field">
            <label>Subject</label>
            <input required value={form.subject} onChange={update('subject')} placeholder="Short summary of the issue" />
          </div>

          <div className="field-row">
            <div className="field">
              <label>Category</label>
              <select required value={form.category_id} onChange={update('category_id')}>
                <option value="">Select category</option>
                {categories.map((c) => (
                  <option key={c.id} value={c.id}>{c.name}</option>
                ))}
              </select>
            </div>
            <div className="field">
              <label>Priority</label>
              <select value={form.priority} onChange={update('priority')}>
                <option>Low</option>
                <option>Medium</option>
                <option>High</option>
                <option>Critical</option>
              </select>
            </div>
          </div>

          <div className="field">
            <label>Description</label>
            <textarea required rows={5} value={form.description} onChange={update('description')} placeholder="Describe the issue in detail" />
          </div>

          <div className="field">
            <label>Attachment (optional — image, PDF, or document, max 5MB)</label>
            <input type="file" onChange={(e) => setFile(e.target.files[0])} />
          </div>

          <button className="btn btn-primary" disabled={loading}>
            {loading ? 'Submitting...' : 'Submit Request'}
          </button>
        </form>
      </div>
    </div>
  )
}
