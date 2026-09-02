import { useEffect, useState } from 'react';
import { api } from '../api.js';
import { MatchCard, Loader, Empty, Stale } from '../components/Shared.jsx';

export default function Fixtures() {
  const [st, setSt] = useState({ loading: true });
  const [f, setF] = useState('all');
  useEffect(() => {
    let on = true;
    api.matches().then(({ data, stale }) => on && setSt({ loading: false, data, stale }))
      .catch(() => on && setSt({ loading: false, error: true }));
    return () => { on = false; };
  }, []);
  if (st.loading) return <Loader />;
  if (st.error) return <Empty icon="wifi_off" title="Connexion impossible" />;
  const data = st.data || [];
  const shown = f === 'all' ? data : data.filter((m) => (f === 'a_venir' ? m.status === 'scheduled' : m.status === 'finished'));
  return (
    <>
      {st.stale && <Stale />}
      <div className="chips" style={{ marginBottom: 12 }}>
        {[['all', 'Tout'], ['a_venir', 'À venir'], ['resultats', 'Résultats']].map(([v, l]) =>
          <span key={v} className={'chip' + (f === v ? ' on' : '')} onClick={() => setF(v)}>{l}</span>)}
      </div>
      {shown.length ? shown.map((m) => <MatchCard key={m.id} m={m} />)
        : <Empty icon="calendar" title="Rien à afficher" subtitle="Aucun match pour ce filtre." />}
    </>
  );
}
