import client from './client'

export const authApi = {
  login: (email, password) => client.post('/api/auth/login.php', { email, password }),
  register: (payload) => client.post('/api/auth/register.php', payload),
  logout: () => client.post('/api/auth/logout.php'),
  me: () => client.get('/api/auth/me.php'),
}

export const categoryApi = {
  list: () => client.get('/api/categories/list.php'),
}

export const technicianApi = {
  list: () => client.get('/api/technicians/list.php'),
}

export const requestApi = {
  list: (params) => client.get('/api/requests/list.php', { params }),
  detail: (id) => client.get('/api/requests/detail.php', { params: { id } }),
  create: (formData) =>
    client.post('/api/requests/create.php', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),
  assign: (request_id, technician_id) =>
    client.post('/api/requests/assign.php', { request_id, technician_id }),
  priority: (request_id, priority) =>
    client.post('/api/requests/priority.php', { request_id, priority }),
  status: (request_id, status, remark) =>
    client.post('/api/requests/status.php', { request_id, status, remark }),
  remark: (request_id, remark) => client.post('/api/requests/remark.php', { request_id, remark }),
}

export const dashboardApi = {
  summary: () => client.get('/api/dashboard/summary.php'),
}
