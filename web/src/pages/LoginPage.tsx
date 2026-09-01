import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { authApi } from '../services/api'

export default function LoginPage() {
  const navigate = useNavigate()
  const [email, setEmail]       = useState('')
  const [password, setPassword] = useState('')
  const [error, setError]       = useState('')
  const [loading, setLoading]   = useState(false)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      const { data } = await authApi.login(email, password)
      localStorage.setItem('gfc_token', data.token)
      navigate('/dashboard')
    } catch (err: any) {
      setError(err.response?.data?.message ?? 'Identifiants incorrects.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div style={{
      minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center',
      background: 'var(--bordeaux-deep)',
    }}>
      <div style={{ width: '100%', maxWidth: 400, padding: '0 24px' }}>
        {/* Logo */}
        <div style={{ textAlign: 'center', marginBottom: 40 }}>
          <svg width="64" height="64" viewBox="0 0 447 447" style={{ display: 'block', margin: '0 auto 16px' }}>
            <rect width="447" height="447" rx="24" fill="#5A1424"/>
            <rect x="91.5" y="85" width="264" height="264" fill="#E8752A"/>
            <rect x="115.5" y="267" width="216" height="28" rx="14" fill="#FFF"/>
          </svg>
          <h1 style={{ color: '#fff', fontSize: 32, marginBottom: 4 }}>GFC 2026</h1>
          <p style={{ color: 'var(--orange-soft)', fontSize: 11, fontWeight: 700, letterSpacing: '0.14em', textTransform: 'uppercase' }}>
            Administration
          </p>
        </div>

        <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <div className="form-group">
            <label style={{ color: 'rgba(255,255,255,0.65)' }}>Email</label>
            <input
              type="email" required autoComplete="email"
              value={email} onChange={e => setEmail(e.target.value)}
              style={{ background: 'rgba(255,255,255,0.08)', borderColor: 'rgba(255,255,255,0.15)', color: '#fff' }}
            />
          </div>
          <div className="form-group">
            <label style={{ color: 'rgba(255,255,255,0.65)' }}>Mot de passe</label>
            <input
              type="password" required autoComplete="current-password"
              value={password} onChange={e => setPassword(e.target.value)}
              style={{ background: 'rgba(255,255,255,0.08)', borderColor: 'rgba(255,255,255,0.15)', color: '#fff' }}
            />
          </div>

          {error && (
            <p style={{ color: '#FCA5A5', fontSize: 13, fontWeight: 600, textAlign: 'center' }}>{error}</p>
          )}

          <button type="submit" className="btn btn-orange" style={{ justifyContent: 'center', marginTop: 8 }} disabled={loading}>
            {loading ? 'Connexion…' : 'Se connecter'}
          </button>
        </form>
      </div>
    </div>
  )
}
