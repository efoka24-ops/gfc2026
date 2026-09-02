import { useEffect, useState } from 'react';
import { api } from '../api.js';
import { Crest, Loader, Empty, Stale } from '../components/Shared.jsx';

export default function Teams() {
  const [st, setSt] = useState({ loading: true });
  const [q, setQ] = useState('');
  useEffect(() => {
    let on = true;
    api.teams().then(({ data, stale }) => on && setSt({ loading: false, data, stale }))
      .catch(() => on && setSt({ loading: false, error: true }));
    return () => { on = false; };
  }, []);
  if (st.loading) return <Loader />;
  if (st.error) return <Empty icon="wifi_off" title="Équipes indisponibles" />;
  const teams = (st.data || []).filter((t) => t.name.toLowerCase().includes(q.toLowerCase()));
  return (
    <>
      {st.stale && <Stale />}
      <input className="card" style={{ width: '100%', font: 'inherit', fontSize: 15 }}
        placeholder="Rechercher une équipe…" value={q} onChange={(e) => setQ(e.target.value)} />
      {teams.length ? (
        <div className="grid">
          {teams.map((t) => (
            <div className="team-card" key={t.id}>
              <Crest team={t} size={38} />
              <div style={{ minWidth: 0 }}>
                <div style={{ fontWeight: 700, color: 'var(--ink)', fontSize: 13, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{t.name}</div>
                {t.city && <div style={{ fontSize: 11, color: 'var(--faint)' }}>{t.city}</div>}
              </div>
            </div>
          ))}
        </div>
      ) : <Empty icon="shield" title="Aucune équipe" />}
    </>
  );
}
