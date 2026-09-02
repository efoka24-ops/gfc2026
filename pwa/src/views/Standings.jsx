import { useEffect, useState } from 'react';
import { api } from '../api.js';
import { Loader, Empty, Stale } from '../components/Shared.jsx';

export default function Standings() {
  const [st, setSt] = useState({ loading: true });
  useEffect(() => {
    let on = true;
    api.standings('championnat').then(({ data, stale }) => on && setSt({ loading: false, data, stale }))
      .catch(() => on && setSt({ loading: false, error: true }));
    return () => { on = false; };
  }, []);
  if (st.loading) return <Loader />;
  if (st.error) return <Empty icon="wifi_off" title="Classement indisponible" />;
  const rows = st.data || [];
  if (!rows.length) return <Empty icon="trophy" title="Classement vide" subtitle="Il sera calculé dès les premiers matchs joués." />;
  return (
    <>
      {st.stale && <Stale />}
      <div className="section-title"><h2>Championnat</h2></div>
      <table className="table">
        <thead><tr><th></th><th style={{ textAlign: 'left' }}>Équipe</th><th>J</th><th>G</th><th>N</th><th>P</th><th>Diff</th><th>Pts</th></tr></thead>
        <tbody>
          {rows.map((r, i) => {
            const rank = r.rank ?? i + 1;
            const qual = r.zone === 'qualification' || rank <= 8;
            const diff = Number(r.goal_difference ?? r.goal_diff ?? 0);
            return (
              <tr key={r.team_id ?? r.id ?? i}>
                <td><span className={'pos' + (qual ? ' qual' : '')}>{rank}</span></td>
                <td className="name">{r.team?.name || r.name || '—'}</td>
                <td>{r.played ?? 0}</td><td>{r.won ?? 0}</td><td>{r.drawn ?? 0}</td><td>{r.lost ?? 0}</td>
                <td style={{ color: diff > 0 ? 'var(--green)' : diff < 0 ? 'var(--red)' : 'var(--muted)', fontWeight: 700 }}>{diff > 0 ? '+' + diff : diff}</td>
                <td className="pts">{r.points ?? 0}</td>
              </tr>
            );
          })}
        </tbody>
      </table>
      <div className="meta" style={{ marginTop: 12 }}><span className="pos qual" style={{ width: 16, height: 16 }} /> Qualifié pour les quarts du Grand Prix Gabriel MBAÏROBÉ</div>
    </>
  );
}
