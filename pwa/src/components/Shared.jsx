import Icon from '../Icon.jsx';

export function Crest({ team, size = 30 }) {
  const color = team?.primary_color || '#7A1F30';
  const abbr = team?.short_name || (team?.name || '?').slice(0, 3).toUpperCase();
  if (team?.logo_url) {
    return <img className="crest" src={team.logo_url} alt="" style={{ width: size, height: size, objectFit: 'cover' }} />;
  }
  return <span className="crest" style={{ width: size, height: size, background: color }}>{abbr}</span>;
}

export function Loader() {
  return <div className="center"><div className="spinner" /><span>Chargement…</span></div>;
}

export function Empty({ title, subtitle, icon = 'info' }) {
  return (
    <div className="center">
      <Icon name={icon} size={30} color="#A1928A" />
      <h2 style={{ color: '#8A7F79' }}>{title}</h2>
      {subtitle && <p style={{ fontSize: 13, maxWidth: 320 }}>{subtitle}</p>}
    </div>
  );
}

export function Stale() {
  return <div className="stale"><Icon name="wifi_off" size={15} color="#8A5A12" /> Données hors ligne, peut-être pas à jour.</div>;
}

const STATUTS = {
  scheduled: 'À venir', live: 'En direct', half_time: 'Mi-temps',
  finished: 'Terminé', postponed: 'Reporté', cancelled: 'Annulé',
};

export function MatchCard({ m }) {
  const live = m.status === 'live' || m.status === 'half_time';
  const played = live || m.status === 'finished';
  const home = m.home_team || {}, away = m.away_team || {};
  const when = m.scheduled_at
    ? new Date(m.scheduled_at).toLocaleString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
    : '';
  return (
    <div className="card match">
      <div className="row spread" style={{ marginBottom: 10 }}>
        <span className="meta" style={{ margin: 0 }}>{m.matchday?.label || ''}</span>
        {live
          ? <span className="badge live"><span className="live-dot" /> {STATUTS[m.status]}{m.minute ? ` ${m.minute}'` : ''}</span>
          : <span className="meta" style={{ margin: 0 }}>{STATUTS[m.status] || ''}</span>}
      </div>
      <div className="teams">
        <div className="team">
          <Crest team={home} />
          <span className="nm">{home.name || '—'}</span>
        </div>
        {played
          ? <span className="score">{m.home_score ?? 0}<span style={{ opacity: .4 }}> - </span>{m.away_score ?? 0}</span>
          : <span className="vs">vs</span>}
        <div className="team away">
          <Crest team={away} />
          <span className="nm">{away.name || '—'}</span>
        </div>
      </div>
      <div className="meta">
        <Icon name="clock" size={13} color="#A1928A" />{when}
        {m.venue && <><Icon name="pin" size={13} color="#A1928A" />{m.venue}</>}
      </div>
    </div>
  );
}
