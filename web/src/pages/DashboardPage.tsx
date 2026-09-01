import { useQuery } from '@tanstack/react-query'
import { matchesApi, standingsApi, teamsApi } from '../services/api'
import { Link } from 'react-router-dom'

export default function DashboardPage() {
  const { data: matches } = useQuery({
    queryKey: ['matches', 'live'],
    queryFn: () => matchesApi.list({ status: 'live' }).then(r => r.data),
    refetchInterval: 15_000,
  })

  const { data: standings } = useQuery({
    queryKey: ['standings'],
    queryFn: () => standingsApi.get().then(r => r.data),
  })

  const { data: teams } = useQuery({
    queryKey: ['teams'],
    queryFn: () => teamsApi.list().then(r => r.data),
  })

  const liveMatches = matches ?? []
  const topStandings = (standings ?? []).slice(0, 5)
  const teamCount = (teams ?? []).length

  return (
    <>
      <div className="topbar">
        <div>
          <div className="topbar-kicker">Garoua Football Challenge</div>
          <div className="topbar-title">Tableau de bord</div>
        </div>
      </div>

      <div className="page-content" style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
        {/* Stats rapides */}
        <div className="grid-4">
          <div className="stat-card">
            <div className="value">{teamCount}</div>
            <div className="label">Équipes</div>
          </div>
          <div className="stat-card" style={{ borderLeftColor: 'var(--green)' }}>
            <div className="value">{liveMatches.length}</div>
            <div className="label">Matchs en direct</div>
          </div>
          <div className="stat-card" style={{ borderLeftColor: 'var(--bordeaux)' }}>
            <div className="value">9</div>
            <div className="label">Journées championnat</div>
          </div>
          <div className="stat-card" style={{ borderLeftColor: '#7C3AED' }}>
            <div className="value">3</div>
            <div className="label">Compétitions</div>
          </div>
        </div>

        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 20 }}>
          {/* Matchs en direct */}
          <div className="card">
            <div className="section-title">
              <h2>Matchs en direct</h2>
              <Link to="/matches" className="btn btn-ghost btn-sm">Tous les matchs</Link>
            </div>
            {liveMatches.length === 0 ? (
              <p style={{ color: 'var(--muted)', fontSize: 13 }}>Aucun match en cours.</p>
            ) : liveMatches.map((m: any) => (
              <Link key={m.id} to={`/matches/${m.id}/live`}>
                <div style={{
                  display: 'flex', alignItems: 'center', gap: 12,
                  padding: '12px 0', borderBottom: '1px solid var(--line)',
                }}>
                  <span className="live-dot" />
                  <span style={{ flex: 1, fontWeight: 700, fontSize: 13 }}>
                    {m.home_team.short_name} {m.home_score}–{m.away_score} {m.away_team.short_name}
                  </span>
                  <span style={{ color: 'var(--orange)', fontWeight: 700, fontSize: 12 }}>{m.minute}'</span>
                </div>
              </Link>
            ))}
          </div>

          {/* Top 5 classement */}
          <div className="card">
            <div className="section-title">
              <h2>Classement — Top 5</h2>
              <Link to="/standings" className="btn btn-ghost btn-sm">Complet</Link>
            </div>
            <div className="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>#</th><th>Équipe</th><th>J</th><th>Pts</th>
                  </tr>
                </thead>
                <tbody>
                  {topStandings.map((s: any) => (
                    <tr key={s.team_id} className={s.rank <= 4 ? 'zone-qualify' : ''}>
                      <td style={{ fontWeight: 700, color: s.rank <= 4 ? 'var(--orange)' : 'var(--muted)' }}>
                        {s.rank}
                      </td>
                      <td style={{ fontWeight: 700 }}>{s.team?.name}</td>
                      <td>{s.played}</td>
                      <td style={{ fontWeight: 800, color: 'var(--bordeaux)' }}>{s.points}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>

        {/* Compétitions */}
        <div className="card">
          <h2 style={{ marginBottom: 16 }}>Compétitions 2025-2026</h2>
          <div className="grid-3">
            {[
              { name: 'Championnat', desc: 'Aller simple · 9 journées · 45 matchs', color: 'var(--orange)', icon: '◆' },
              { name: 'GP Gabriel MBAÏROBÉ', desc: 'Quarts de finale · 8 meilleurs du championnat', color: 'var(--bordeaux)', icon: '◈' },
              { name: 'Super Coupe', desc: 'Match unique · Vainqueur GP vs Vainqueur Champ.', color: '#7C3AED', icon: '★' },
            ].map(c => (
              <div key={c.name} style={{
                padding: '16px', borderRadius: 'var(--radius-md)',
                borderLeft: `4px solid ${c.color}`, background: 'var(--cream)',
              }}>
                <div style={{ fontFamily: 'Anton', fontSize: 15, color: c.color, marginBottom: 6 }}>{c.name}</div>
                <div style={{ fontSize: 12, color: 'var(--muted)', lineHeight: 1.5 }}>{c.desc}</div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </>
  )
}
