import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/service-request-system/backend'

const client = axios.create({
  baseURL: API_URL,
  withCredentials: true, // send/receive the PHP session cookie
})

// Normalize error messages so components can just read err.message
client.interceptors.response.use(
  (res) => res.data,
  (err) => {
    const message =
      err.response?.data?.message ||
      (err.response?.data?.errors && err.response.data.errors.join(', ')) ||
      err.message ||
      'Something went wrong.'
    return Promise.reject({ ...err, message })
  }
)

export default client
