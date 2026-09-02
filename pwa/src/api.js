// Client API. URL racine-relative (/api) : herite du protocole et de l'hote de
// la page, donc fonctionne en http comme en https sans reconfiguration.
const BASE = '/api';
const memo = new Map();
const TTL = 45000;

async function get(path) {
  const key = 'gfc:' + path;
  const hit = memo.get(key);
  if (hit && Date.now() - hit.at < TTL) return { data: hit.data, stale: false };
  try {
    const res = await fetch(BASE + path, { headers: { Accept: 'application/json' } });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();
    memo.set(key, { at: Date.now(), data });
    try { localStorage.setItem(key, JSON.stringify(data)); } catch (e) {}
    return { data, stale: false };
  } catch (e) {
    // Repli hors-ligne : dernier contenu connu, signale comme non a jour.
    try {
      const s = localStorage.getItem(key);
      if (s) return { data: JSON.parse(s), stale: true };
    } catch (_) {}
    throw e;
  }
}

export const api = {
  teams: () => get('/teams'),
  matches: () => get('/matches'),
  competitions: () => get('/competitions'),
  standings: (slug = 'championnat') => get('/standings?competition=' + slug),
  players: (teamId) => get('/players' + (teamId ? '?team_id=' + teamId : '')),
};
