import { useQuery } from '@tanstack/react-query'
import { useState } from 'react'
import { standingsApi } from '../services/api'

const COMPETITIONS = [
  { slug: 'championnat', label: 'Championnat' },
  { slug: 'gp_gabriel',  label: 'GP Gabriel MBAÏROBÉ' },
]

export default function StandingsPage() {
  const [comp, setComp] = useState('championnat')

  const { data: standings = [], isLoading } = useQuery({
    queryKey: ['standings', comp],
    queryFn: () => standingsApi.get(comp).then(r => r.data),
  })

  return (
    <>
      <div className="topbar">
        <div>
          <div className="topbar-kicker">Championnat 2025-2026</div>
          <div className="topbar-title">Classement</div>
        </div>
      </div>

      <div className="page-content" style={{ display: 'flex', flexDirection: 'column', gap: 20 }}>
        {/* Sélecteur compétition */}
        <div style={{ display: 'flex', gap: 8 }}>
          {COMPETITIONS.map(c => (
            <button key={c.slug} onClick={() => setComp(c.slug)}
              className={`btn btn-sm ${comp === c.slug ? 'btn-primary' : 'btn-ghost'}`}>
              {c.label}
            </button>
          ))}
        </div>

        {/* Légende zones */}
        <div style={{ display: 'flex', gap: 20, fontSize: 11, fontWeight: 700, color: 'var(--muted)' }}>
          <span style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
            <span style={{ width: 10, height: 10, borderRadius: 2, background: 'var(--orange)', display: 'inline-block' }} />
            Qualifiés GP Gabriel (top 8)
          </span>
        </div>

        <div className="card">
          <div className="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Équipe</th>
                  <th title="Matchs joués">J</th>
                  <th title="Victoires">V</th>
                  <th title="Nuls">N</th>
                  <th title="Défaites">D</th>
                  <th title="Buts pour">BP</th>
                  <th title="Buts contre">BC</th>
                  <th title="Différence de buts">DB</th>
                  <th title="Points">Pts</th>
                </tr>
              </thead>
              <tbody>
                {isLoading ? (
                  <tr><td colSpan={10} style={{ textAlign: 'center', padding: 32, color: 'var(--muted)' }}>Chargement…</td></tr>
                ) : standings.map((s: any) => {
                  const isGP = s.rank <= 8   // top 8 qualifié pour GP Gabriel
                  return (
                    <tr key={s.team_id} className={isGP ? 'zone-qualify' : 'zone-safe'}>
                      <td style={{ fontWeight: 800, color: isGP ? 'var(--orange)' : 'var(--muted)', width: 32 }}>
                        {s.rank}
                      </td>
                      <td style={{ fontWeight: 700 }}>{s.team?.name}</td>
                      <td>{s.played}</td>
                      <td style={{ color: 'var(--green)', fontWeight: 700 }}>{s.won}</td>
                      <td>{s.drawn}</td>
                      <td style={{ color: 'var(--red)' }}>{s.lost}</td>
                      <td>{s.goals_for}</td>
                      <td>{s.goals_against}</td>
                      <td style={{ color: s.goal_difference > 0 ? 'var(--green)' : s.goal_difference < 0 ? 'var(--red)' : 'inherit' }}>
                        {s.goal_difference > 0 ? '+' : ''}{s.goal_difference}
                      </td>
                      <td style={{ fontFamily: 'Anton', fontSize: 18, color: 'var(--bordeaux)' }}>{s.points}</td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </>
  )
}
