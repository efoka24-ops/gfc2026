import { useQuery } from '@tanstack/react-query'
import { teamsApi } from '../services/api'

export default function TeamsPage() {
  const { data: teams = [], isLoading } = useQuery({
    queryKey: ['teams'],
    queryFn: () => teamsApi.list().then(r => r.data),
  })

  return (
    <>
      <div className="topbar">
        <div>
          <div className="topbar-kicker">10 équipes engagées</div>
          <div className="topbar-title">Équipes</div>
        </div>
      </div>

      <div className="page-content">
        {isLoading ? (
          <p style={{ color: 'var(--muted)' }}>Chargement…</p>
        ) : (
          <div className="grid-2" style={{ gap: 16 }}>
            {teams.map((t: any) => (
              <div key={t.id} className="card" style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
                {/* Crest placeholder */}
                <div style={{
                  width: 48, height: 48, borderRadius: 8, flexShrink: 0,
                  background: t.primary_color ?? 'var(--bordeaux)',
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                }}>
                  <span style={{ fontFamily: 'Anton', fontSize: 15, color: '#fff', letterSpacing: 1 }}>
                    {t.short_name}
                  </span>
                </div>
                <div style={{ flex: 1 }}>
                  <div style={{ fontWeight: 800, fontSize: 14, color: 'var(--ink)' }}>{t.name}</div>
                  {t.city && <div style={{ fontSize: 12, color: 'var(--muted)', marginTop: 2 }}>{t.city}</div>}
                </div>
                <div style={{
                  width: 10, height: 10, borderRadius: 2,
                  background: t.active ? 'var(--green)' : 'var(--red)',
                }} title={t.active ? 'Active' : 'Inactive'} />
              </div>
            ))}
          </div>
        )}
      </div>
    </>
  )
}
