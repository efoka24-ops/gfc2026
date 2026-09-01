import axios from 'axios'

const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? '/api',
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
})

// Injecter token
http.interceptors.request.use((config) => {
  const token = localStorage.getItem('gfc_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Rediriger vers login si 401
http.interceptors.response.use(
  (r) => r,
  (err) => {
    if (err.response?.status === 401) {
      localStorage.removeItem('gfc_token')
      window.location.href = '/login'
    }
    return Promise.reject(err)
  }
)

export default http

// ── Auth ──────────────────────────────────────────────────────
export const authApi = {
  login:  (email: string, password: string) =>
    http.post('/auth/login', { email, password, device_name: 'web' }),
  logout: () => http.post('/auth/logout'),
  me:     () => http.get('/auth/me'),
}

// ── Dashboard ─────────────────────────────────────────────────
export const dashboardApi = {
  stats: () => http.get('/dashboard/stats'),
}

// ── Équipes ───────────────────────────────────────────────────
export const teamsApi = {
  list:    ()                  => http.get('/teams'),
  get:     (id: number)        => http.get(`/teams/${id}`),
  create:  (data: object)      => http.post('/teams', data),
  update:  (id: number, data: object) => http.put(`/teams/${id}`, data),
  remove:  (id: number)        => http.delete(`/teams/${id}`),
}

// ── Matchs ────────────────────────────────────────────────────
export const matchesApi = {
  list:         (params?: object) => http.get('/matches', { params }),
  get:          (id: number)      => http.get(`/matches/${id}`),
  create:       (data: object)    => http.post('/matches', data),

  // Contrôle live
  start:        (id: number) => http.post(`/matches/${id}/start`),
  halfTime:     (id: number) => http.post(`/matches/${id}/half-time`),
  resume:       (id: number) => http.post(`/matches/${id}/resume`),
  finish:       (id: number) => http.post(`/matches/${id}/finish`),
  updateMinute: (id: number, minute: number) => http.patch(`/matches/${id}/minute`, { minute }),

  // Événements
  addEvent:    (matchId: number, event: object) => http.post(`/matches/${matchId}/events`, event),
  deleteEvent: (matchId: number, eventId: number) => http.delete(`/matches/${matchId}/events/${eventId}`),
}

// ── Classement ────────────────────────────────────────────────
export const standingsApi = {
  get: (competition = 'championnat') => http.get('/standings', { params: { competition } }),
}
