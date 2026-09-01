import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useState } from 'react'
import { Link } from 'react-router-dom'
import { matchesApi } from '../services/api'

const STATUS_LABEL: Record<string, string> = {
  scheduled: 'Programmé', live: 'En direct', half_time: 'Mi-temps',
  finished: 'Terminé', postponed: 'Reporté', cancelled: 'Annulé',
}
const STATUS_BADGE: Record<string, string> = {
  scheduled: 'badge-scheduled', live: 'badge-live',
  half_time: 'badge-halftime', finished: 'badge-finished',
}

export default function MatchesPage() {
  const qc = useQueryClient()
  const [filter, setFilter] = useState<string>('')

  const { data: matches = [], isLoading } = useQuery({
    queryKey: ['matches', filter],
    queryFn: () => matchesApi.list(filter ? { status: filter } : {}).then(r => r.data),
    refetchInterval: 20_000,
  })

  return (
    <>
      <div className="topbar">
        <div>
          <div className="topbar-kicker">Administration</div>
          <div className="topbar-title">Matchs</div>
        </div>
      </div>

      <div className="page-content" style={{ display: 'flex', flexDirection: 'column', gap: 20 }}>
        {/* Filtres */}
        <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
          {['', 'live', 'scheduled', 'finished'].map(s => (
            <button key={s} onClick={() => setFilter(s)}
              className={`btn btn-sm ${filter === s ? 'btn-primary' : 'btn-ghost'}`}>
              {s === '' ? 'Tous' : STATUS_LABEL[s]}
            </button>
          ))}
        </div>

        <div className="card">
          <div className="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Statut</th>
                  <th>Compétition</th>
                  <th>Domicile</th>
                  <th>Score</th>
                  <th>Extérieur</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {isLoading ? (
                  <tr><td colSpan={7} style={{ textAlign: 'center', padding: 32, color: 'var(--muted)' }}>Chargement…</td></tr>
                ) : matches.map((m: any) => (
                  <tr key={m.id}>
                    <td>
                      <span className={`badge ${STATUS_BADGE[m.status] ?? 'badge-scheduled'}`}>
                        {m.status === 'live' && <span className="live-dot" style={{ width: 6, height: 6 }} />}
                        {STATUS_LABEL[m.status]}
                        {m.status === 'live' && ` ${m.minute}'`}
                      </span>
                    </td>
                    <td style={{ fontSize: 11, color: 'var(--muted)', fontWeight: 700 }}>
                      {m.competition?.name ?? '—'}
                    </td>
                    <td style={{ fontWeight: 700 }}>{m.home_team?.name}</td>
                    <td style={{ fontFamily: 'Anton', fontSize: 18, color: 'var(--bordeaux)', textAlign: 'center' }}>
                      {m.status === 'scheduled' ? '–' : `${m.home_score} – ${m.away_score}`}
                    </td>
                    <td style={{ fontWeight: 700 }}>{m.away_team?.name}</td>
                    <td style={{ fontSize: 12, color: 'var(--muted)' }}>
                      {new Date(m.scheduled_at).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })}
                    </td>
                    <td>
                      {['scheduled', 'live', 'half_time'].includes(m.status) && (
                        <Link to={`/matches/${m.id}/live`} className="btn btn-orange btn-sm">
                          Gérer
                        </Link>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </>
  )
}
