import axios from 'axios';
import * as SecureStore from 'expo-secure-store';

const API_URL = process.env.EXPO_PUBLIC_API_URL ?? 'http://localhost/api';

export const api = axios.create({
  baseURL: API_URL,
  headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
  timeout: 10000,
});

// Injecter le token automatiquement
api.interceptors.request.use(async (config) => {
  const token = await SecureStore.getItemAsync('gfc_token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// ── Auth ──────────────────────────────────────────────────────
export const login = async (email: string, password: string) => {
  const { data } = await api.post('/auth/login', { email, password, device_name: 'mobile' });
  await SecureStore.setItemAsync('gfc_token', data.token);
  await SecureStore.setItemAsync('gfc_user', JSON.stringify(data.user));
  return data;
};

export const logout = async () => {
  await api.post('/auth/logout');
  await SecureStore.deleteItemAsync('gfc_token');
  await SecureStore.deleteItemAsync('gfc_user');
};

// ── Matchs ────────────────────────────────────────────────────
export const getMatches  = (params?: object) => api.get('/matches', { params });
export const getMatch    = (id: number)       => api.get(`/matches/${id}`);
export const startMatch  = (id: number)       => api.post(`/matches/${id}/start`);
export const halfTime    = (id: number)       => api.post(`/matches/${id}/half-time`);
export const resumeMatch = (id: number)       => api.post(`/matches/${id}/resume`);
export const finishMatch = (id: number)       => api.post(`/matches/${id}/finish`);
export const updateMinute = (id: number, minute: number) =>
  api.patch(`/matches/${id}/minute`, { minute });
export const addEvent = (matchId: number, event: object) =>
  api.post(`/matches/${matchId}/events`, event);
export const deleteEvent = (matchId: number, eventId: number) =>
  api.delete(`/matches/${matchId}/events/${eventId}`);

// ── Équipes & classement ──────────────────────────────────────
export const getTeams    = ()           => api.get('/teams');
export const getStandings = (seasonId: number) => api.get(`/standings?season_id=${seasonId}`);
