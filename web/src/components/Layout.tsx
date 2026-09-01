import { Outlet, NavLink, useNavigate } from 'react-router-dom'
import { authApi } from '../services/api'

const NAV = [
  { to: '/dashboard', label: 'Tableau de bord', icon: HomeIcon },
  { to: '/matches',   label: 'Matchs',          icon: CalendarIcon },
  { to: '/standings', label: 'Classement',       icon: TrophyIcon },
  { to: '/teams',     label: 'Équipes',          icon: ShieldIcon },
]

export default function Layout() {
  const navigate = useNavigate()

  const handleLogout = async () => {
    try { await authApi.logout() } catch {}
    localStorage.removeItem('gfc_token')
    navigate('/login')
  }

  return (
    <div className="layout">
      <aside className="sidebar">
        <div className="sidebar-logo">
          <svg width="34" height="34" viewBox="0 0 447 447" fill="none">
            <rect width="447" height="447" fill="#3E0B18"/>
            <rect x="91.5" y="85" width="264" height="264" fill="#E8752A"/>
            <rect x="115.5" y="267" width="216" height="28" rx="14" fill="#FFF"/>
          </svg>
          <div>
            <div className="sidebar-logo-text">GFC</div>
            <div className="sidebar-logo-sub">Dashboard 2026</div>
          </div>
        </div>

        <div className="nav-section-label">Navigation</div>
        {NAV.map(({ to, label, icon: Icon }) => (
          <NavLink key={to} to={to} className={({ isActive }) => `nav-item${isActive ? ' active' : ''}`}>
            <Icon size={18} />
            {label}
          </NavLink>
        ))}

        <div style={{ marginTop: 'auto', padding: '20px' }}>
          <button className="btn btn-ghost" style={{ width: '100%', color: 'rgba(255,255,255,0.5)' }} onClick={handleLogout}>
            <LogoutIcon size={16} />
            Déconnexion
          </button>
        </div>
      </aside>

      <main className="main">
        <Outlet />
      </main>
    </div>
  )
}

// ── Icones SVG inline (pas d'emojis) ─────────────────────────
function HomeIcon({ size = 20 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/>
      <path d="M9 21V12h6v9"/>
    </svg>
  )
}
function CalendarIcon({ size = 20 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
    </svg>
  )
}
function TrophyIcon({ size = 20 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2z"/>
    </svg>
  )
}
function ShieldIcon({ size = 20 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
    </svg>
  )
}
function LogoutIcon({ size = 20 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
    </svg>
  )
}
