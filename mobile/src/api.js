/**
 * GFC 2026 — Client API
 * Connecté au backend Laravel / Sanctum
 * Supporte : championnat, GP Gabriel MBAÏROBÉ, Super Coupe
 */
import Constants from 'expo-constants';
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as SecureStore from 'expo-secure-store';
import { useState, useEffect } from 'react';

const BASE = Constants.expoConfig?.extra?.apiUrl ?? 'http://10.0.2.2:8000/api';
const CACHE_TTL = 60 * 1000; // 60 s

const memory = new Map();

// ── Helpers ──────────────────────────────────────────────────
async function getToken() {
  try { return await SecureStore.getItemAsync('gfc_token'); } catch { return null; }
}

async function request(path, options = {}) {
  const { cache = true, method = 'GET', body, auth = false } = options;
  const key = 'gfc:' + path;

  if (cache && method === 'GET') {
    const hit = memory.get(key);
    if (hit && Date.now() - hit.at < CACHE_TTL) return hit.data;
  }

  const headers = { Accept: 'application/json', 'Content-Type': 'application/json' };
  if (auth) {
    const token = await getToken();
    if (token) headers['Authorization'] = 'Bearer ' + token;
  }

  try {
    const res = await fetch(BASE + path, {
      method,
      headers,
      body: body ? JSON.stringify(body) : undefined,
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw Object.assign(new Error(err.message ?? 'HTTP ' + res.status), { status: res.status, data: err });
    }
    const data = method === 'DELETE' ? null : await res.json();
    if (cache && method === 'GET') {
      memory.set(key, { at: Date.now(), data });
      await AsyncStorage.setItem(key, JSON.stringify(data));
    }
    return data;
  } catch (err) {
    if (method === 'GET') {
      const stored = await AsyncStorage.getItem(key);
      if (stored) return JSON.parse(stored);
    }
    throw err;
  }
}

function invalidate(...patterns) {
  for (const [k] of memory) {
    if (patterns.some((p) => k.includes(p))) memory.delete(k);
  }
}

// ── API publique (lecture) ────────────────────────────────────
export const api = {
  competitions:  ()                           => request('/competitions'),
  fixtures:      (comp, scope = 'upcoming')   => request(`/matches?scope=${scope}&competition=${comp ?? ''}`),
  results:       (comp)                       => request(`/matches?scope=results&competition=${comp ?? ''}`),
  match:         (id)                         => request('/matches/' + id, { cache: false }),
  standings:     (comp = 'championnat')       => request('/standings?competition=' + comp),
  gpBracket:     ()                           => request('/gp-gabriel/bracket'),
  teams:         ()                           => request('/teams'),
  team:          (id)                         => request('/teams/' + id),
  player:        (id)                         => request('/players/' + id),
  playerStats:   (metric = 'goals', comp = 'championnat') =>
                   request(`/stats/players?metric=${metric}&competition=${comp}`),
  teamStats:     (comp = 'championnat')       => request('/stats/teams?competition=' + comp),
  news:          (limit = 20)                 => request('/news?limit=' + limit),
  media:         (type)                       => request('/media' + (type ? '?type=' + type : '')),
  registerDevice: (token, platform, teamId)   =>
    request('/devices', { method: 'POST', body: { token, platform, team_id: teamId } }),
};

// ── API backoffice (auth requise — secrétaire terrain) ────────
export const adminApi = {
  login:  (email, password) =>
    request('/auth/login', { method: 'POST', body: { email, password, device_name: 'mobile' }, cache: false }),
  logout: () => request('/auth/logout', { method: 'POST', auth: true, cache: false }),
  me:     () => request('/auth/me', { auth: true, cache: false }),

  startMatch:   (id)     => request(`/matches/${id}/start`,     { method: 'POST', auth: true, cache: false }).then(r => { invalidate('/matches'); return r; }),
  halfTime:     (id)     => request(`/matches/${id}/half-time`, { method: 'POST', auth: true, cache: false }).then(r => { invalidate('/matches'); return r; }),
  resumeMatch:  (id)     => request(`/matches/${id}/resume`,    { method: 'POST', auth: true, cache: false }).then(r => { invalidate('/matches'); return r; }),
  finishMatch:  (id)     => request(`/matches/${id}/finish`,    { method: 'POST', auth: true, cache: false }).then(r => { invalidate('/matches', '/standings'); return r; }),
  updateMinute: (id, m)  => request(`/matches/${id}/minute`,   { method: 'PATCH', body: { minute: m }, auth: true, cache: false }),
  addEvent:     (id, ev) => request(`/matches/${id}/events`,   { method: 'POST', body: ev, auth: true, cache: false }).then(r => { invalidate('/matches/' + id); return r; }),
  deleteEvent:  (mid, eid) => request(`/matches/${mid}/events/${eid}`, { method: 'DELETE', auth: true, cache: false }),
};

// ── Hooks ─────────────────────────────────────────────────────
export function usePolling(fn, deps, intervalMs = 15000) {
  const [state, setState] = useState({ data: null, loading: true, error: null });

  useEffect(() => {
    let alive = true;
    let timer;
    const load = async () => {
      try {
        const data = await fn();
        if (alive) setState({ data, loading: false, error: null });
      } catch (error) {
        if (alive) setState((s) => ({ data: s.data, loading: false, error }));
      }
      if (alive && intervalMs) timer = setTimeout(load, intervalMs);
    };
    load();
    return () => { alive = false; clearTimeout(timer); };
  }, deps);

  return state;
}

export function useQuery(fn, deps = []) {
  return usePolling(fn, deps, 0);
}


export const api = {
  competitions:  ()                      => request('/competitions'),
  news:          (limit = 20)            => request('/news?limit=' + limit),
  media:         (type)                  => request('/media' + (type ? '?type=' + type : '')),
  fixtures:      (competition)           => request('/matches?scope=upcoming' + (competition ? '&competition=' + competition : '')),
  results:       (competition)           => request('/matches?scope=results' + (competition ? '&competition=' + competition : '')),
  match:         (id)                    => request('/matches/' + id, { cache: false }),
  standings:     (competition = 'championnat') => request('/standings?competition=' + competition),
  teams:         ()                      => request('/teams'),
  team:          (id)                    => request('/teams/' + id),
  player:        (id)                    => request('/players/' + id),
  playerStats:   (metric = 'goals', competition = 'championnat') =>
                   request('/stats/players?metric=' + metric + '&competition=' + competition),
  teamStats:     (competition = 'championnat') => request('/stats/teams?competition=' + competition),
  registerDevice: (token, platform, teamId) =>
    fetch(BASE + '/devices', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token, platform, team_id: teamId }),
    }),
};

/** Rafraîchissement automatique — utilisé par la fiche match en direct. */
export function usePolling(fn, deps, intervalMs = 15000) {
  const { useState, useEffect } = require('react');
  const [state, setState] = useState({ data: null, loading: true, error: null });

  useEffect(() => {
    let alive = true;
    let timer;

    const load = async () => {
      try {
        const data = await fn();
        if (alive) setState({ data, loading: false, error: null });
      } catch (error) {
        if (alive) setState((s) => ({ data: s.data, loading: false, error }));
      }
      if (alive && intervalMs) timer = setTimeout(load, intervalMs);
    };

    load();
    return () => { alive = false; clearTimeout(timer); };
  }, deps);

  return state;
}

export function useQuery(fn, deps = []) {
  return usePolling(fn, deps, 0);
}
