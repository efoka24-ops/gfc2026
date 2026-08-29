import Constants from 'expo-constants';
import AsyncStorage from '@react-native-async-storage/async-storage';

const BASE = Constants.expoConfig?.extra?.apiUrl ?? 'http://10.0.2.2:8000/api';
const TTL = 60 * 1000; // cache mémoire court, l'app reste utilisable en réseau faible

const memory = new Map();

async function request(path, { cache = true } = {}) {
  const key = 'gfc:' + path;
  const hit = memory.get(key);
  if (cache && hit && Date.now() - hit.at < TTL) return hit.data;

  try {
    const res = await fetch(BASE + path, { headers: { Accept: 'application/json' } });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();
    memory.set(key, { at: Date.now(), data });
    await AsyncStorage.setItem(key, JSON.stringify(data));
    return data;
  } catch (err) {
    // mode hors-ligne : dernière réponse connue
    const stored = await AsyncStorage.getItem(key);
    if (stored) return JSON.parse(stored);
    throw err;
  }
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
