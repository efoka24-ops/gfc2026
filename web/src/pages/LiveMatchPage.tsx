import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useParams, useNavigate } from 'react-router-dom'
import { useState } from 'react'
import { matchesApi } from '../services/api'

const EVENT_TYPES = [
  { key: 'goal',              label: 'But' },
  { key: 'own_goal',          label: 'But contre son camp' },
  { key: 'yellow_card',       label: 'Carton jaune' },
  { key: 'red_card',          label: 'Carton rouge' },
  { key: 'yellow_red_card',   label: '2e jaune / rouge' },
  { key: 'substitution_in',   label: 'Entrée' },
  { key: 'substitution_out',  label: 'Sortie' },
  { key: 'penalty_scored',    label: 'Penalty marqué' },
  { key: 'penalty_missed',    label: 'Penalty manqué' },
]

const EVENT_COLOR: Record<string, string> = {
  goal: 'var(--green)', penalty_scored: 'var(--green)',
  yellow_card: 'var(--yellow)', yellow_red_card: 'var(--yellow)',
  red_card: 'var(--red)', own_goal: 'var(--red)', penalty_missed: 'var(--muted)',
  substitution_in: '#3B82F6', substitution_out: '#3B82F6',
}

export default function LiveMatchPage() {
  const { id } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const qc = useQueryClient()
  const matchId = Number(id)

  const { data: match, isLoading } = useQuery({
    queryKey: ['match', matchId],
    queryFn: () => matchesApi.get(matchId).then(r => r.data),
    refetchInterval: 10_000,
  })

  const invalidate = () => qc.invalidateQueries({ queryKey: ['match', matchId] })

  const ctrl = (action: () => Promise<any>) =>
    action().then(invalidate).catch(err => alert(err?.response?.data?.message ?? 'Erreur'))

  // Formulaire événement
  const [eventType, setEventType]   = useState('goal')
  const [minute, setMinute]         = useState('')
  const [teamId, setTeamId]         = useState('')
  const [playerId, setPlayerId]     = useState('')
  const [submitting, setSubmitting] = useState(false)

  const submitEvent = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!minute || !teamId) return alert('Minute et équipe obligatoires')
    setSubmitting(true)
    try {
      await matchesApi.addEvent(matchId, {
        type: eventType, minute: Number(minute),
        team_id: Number(teamId),
        player_id: playerId ? Number(playerId) : null,
      })
      setMinute(''); setPlayerId('')
      invalidate()
    } catch (err: any) {
      alert(err?.response?.data?.message ?? 'Erreur')
    } finally { setSubmitting(false) }
  }

  if (isLoading || !match) return <div style={{ padding: 40 }}>Chargement…</div>

  const homePlayers = (match.lineups ?? []).filter((l: any) => l.team_id === match.home_team_id)
  const awayPlayers = (match.lineups ?? []).filter((l: any) => l.team_id === match.away_team_id)
  const allPlayers  = teamId === String(match.home_team_id) ? homePlayers : awayPlayers

  return (
    <>
      <div className="topbar">
        <button onClick={() => navigate('/matches')} className="btn btn-ghost btn-sm" style={{ marginRight: 8 }}>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
          Retour
        </button>
        <div>
          <div className="topbar-kicker">{match.competition?.name ?? 'Match'}</div>
          <div className="topbar-title">{match.home_team?.short_name} vs {match.away_team?.short_name}</div>
        </div>
      </div>

      <div className="page-content" style={{ display: 'grid', gridTemplateColumns: '1fr 380px', gap: 20, alignItems: 'start' }}>
        {/* Panneau gauche : score + contrôles + événements */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          {/* Score card */}
          <div className="card" style={{ background: 'var(--bordeaux-mid)', color: '#fff' }}>
            <div style={{ textAlign: 'center', marginBottom: 12 }}>
              {match.status === 'live' && (
                <span className="badge badge-live">
                  <span className="live-dot" />
                  En direct — {match.minute}'
                </span>
              )}
              {match.status === 'half_time' && <span className="badge badge-halftime">Mi-temps</span>}
              {match.status === 'finished' && <span className="badge badge-finished">Terminé</span>}
              {match.status === 'scheduled' && <span className="badge badge-scheduled">Programmé</span>}
            </div>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-around', padding: '8px 0' }}>
              <div style={{ textAlign: 'center', flex: 1 }}>
                <div style={{ fontSize: 14, fontWeight: 700, marginBottom: 4 }}>{match.home_team?.name}</div>
              </div>
              <div style={{ fontFamily: 'Anton', fontSize: 56, color: '#fff', letterSpacing: 4 }}>
                {match.home_score} – {match.away_score}
              </div>
              <div style={{ textAlign: 'center', flex: 1 }}>
                <div style={{ fontSize: 14, fontWeight: 700, marginBottom: 4 }}>{match.away_team?.name}</div>
              </div>
            </div>
          </div>

          {/* Boutons de contrôle */}
          <div className="card">
            <h2 style={{ marginBottom: 14, fontSize: 15 }}>Contrôle du match</h2>
            <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap' }}>
              {match.status === 'scheduled' && (
                <button className="btn btn-green" onClick={() => ctrl(() => matchesApi.start(matchId))}>
                  Démarrer le match
                </button>
              )}
              {match.status === 'live' && (
                <button className="btn btn-orange" onClick={() => ctrl(() => matchesApi.halfTime(matchId))}>
                  Siffler mi-temps
                </button>
              )}
              {match.status === 'half_time' && (
                <button className="btn btn-green" onClick={() => ctrl(() => matchesApi.resume(matchId))}>
                  Reprendre (2e mi-temps)
                </button>
              )}
              {['live', 'half_time'].includes(match.status) && (
                <button className="btn btn-primary" onClick={() => {
                  if (confirm('Terminer définitivement ce match ?')) ctrl(() => matchesApi.finish(matchId))
                }}>
                  Terminer le match
                </button>
              )}
            </div>
          </div>

          {/* Événements */}
          <div className="card">
            <h2 style={{ marginBottom: 14, fontSize: 15 }}>Événements</h2>
            {(match.events ?? []).length === 0 ? (
              <p style={{ color: 'var(--muted)', fontSize: 13 }}>Aucun événement.</p>
            ) : (
              <div style={{ display: 'flex', flexDirection: 'column', gap: 0 }}>
                {[...(match.events ?? [])].sort((a: any, b: any) => b.minute - a.minute).map((ev: any) => (
                  <div key={ev.id} style={{
                    display: 'flex', alignItems: 'center', gap: 12,
                    padding: '10px 0', borderBottom: '1px solid var(--line)',
                  }}>
                    <div style={{
                      width: 28, height: 28, borderRadius: '50%',
                      background: EVENT_COLOR[ev.type] ?? 'var(--muted)',
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                      flexShrink: 0,
                    }}>
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="white" stroke="none">
                        <circle cx="12" cy="12" r="8"/>
                      </svg>
                    </div>
                    <div style={{ flex: 1 }}>
                      <div style={{ fontWeight: 700, fontSize: 13 }}>
                        {ev.minute}' — {EVENT_TYPES.find(t => t.key === ev.type)?.label ?? ev.type}
                      </div>
                      {ev.player && <div style={{ fontSize: 11, color: 'var(--muted)' }}>{ev.player.last_name} {ev.player.first_name}</div>}
                    </div>
                    <button onClick={() => {
                      if (confirm('Supprimer cet événement ?'))
                        matchesApi.deleteEvent(matchId, ev.id).then(invalidate)
                    }} className="btn btn-ghost btn-sm" style={{ color: 'var(--red)' }}>
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    </button>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Panneau droit : formulaire événement */}
        {['scheduled', 'live', 'half_time'].includes(match.status) && (
          <div className="card" style={{ position: 'sticky', top: 80 }}>
            <h2 style={{ marginBottom: 16, fontSize: 15 }}>Saisir un événement</h2>
            <form onSubmit={submitEvent} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
              <div className="form-group">
                <label>Type d'événement</label>
                <select value={eventType} onChange={e => setEventType(e.target.value)}>
                  {EVENT_TYPES.map(t => <option key={t.key} value={t.key}>{t.label}</option>)}
                </select>
              </div>
              <div className="form-group">
                <label>Minute *</label>
                <input type="number" min={1} max={120} value={minute} onChange={e => setMinute(e.target.value)} placeholder="ex : 45" required />
              </div>
              <div className="form-group">
                <label>Équipe *</label>
                <select value={teamId} onChange={e => setTeamId(e.target.value)} required>
                  <option value="">— Choisir —</option>
                  <option value={match.home_team_id}>{match.home_team?.name}</option>
                  <option value={match.away_team_id}>{match.away_team?.name}</option>
                </select>
              </div>
              {teamId && allPlayers.length > 0 && (
                <div className="form-group">
                  <label>Joueur</label>
                  <select value={playerId} onChange={e => setPlayerId(e.target.value)}>
                    <option value="">— Facultatif —</option>
                    {allPlayers.map((l: any) => (
                      <option key={l.player_id} value={l.player_id}>
                        #{l.player?.jersey_number} {l.player?.last_name} {l.player?.first_name}
                      </option>
                    ))}
                  </select>
                </div>
              )}
              <button type="submit" className="btn btn-bordeaux" disabled={submitting}
                style={{ background: 'var(--bordeaux)', color: '#fff', justifyContent: 'center' }}>
                {submitting ? 'Enregistrement…' : 'Valider l\'événement'}
              </button>
            </form>
          </div>
        )}
      </div>
    </>
  )
}
