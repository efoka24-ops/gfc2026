import { useEffect, useState } from 'react';
import { api } from '../api.js';
import { MatchCard, Loader, Empty, Stale } from '../components/Shared.jsx';

export default function Home() {
  const [st, setSt] = useState({ loading: true });
  useEffect(() => {
    let on = true;
    const load = () => api.matches()
      .then(({ data, stale }) => on && setSt({ loading: false, data, stale }))
      .catch(() => on && setSt({ loading: false, error: true }));
    load();
    const t = setInterval(load, 15000); // direct : rafraichissement 15 s
    return () => { on = false; clearInterval(t); };
  }, []);

  if (st.loading) return <Loader />;
  if (st.error) return <Empty icon="wifi_off" title="Connexion impossible" subtitle="Impossible de joindre le serveur. La page se rechargera automatiquement." />;

  const all = st.data || [];
  const live = all.filter((m) => m.status === 'live' || m.status === 'half_time');
  const upcoming = all.filter((m) => m.status === 'scheduled').slice(0, 5);
  const recent = all.filter((m) => m.status === 'finished').slice(-5).reverse();

  return (
    <>
      {st.stale && <Stale />}
      {live.length > 0 && <><div className="section-title"><h2>En direct</h2></div>{live.map((m) => <MatchCard key={m.id} m={m} />)}</>}
      <div className="section-title"><h2>Prochains matchs</h2></div>
      {upcoming.length ? upcoming.map((m) => <MatchCard key={m.id} m={m} />)
        : <Empty icon="calendar" title="Aucun match programmé" subtitle="Le calendrier apparaîtra ici dès qu'il sera publié." />}
      {recent.length > 0 && <><div className="section-title"><h2>Derniers résultats</h2></div>{recent.map((m) => <MatchCard key={m.id} m={m} />)}</>}
    </>
  );
}
