import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import Layout from './components/Layout'
import LoginPage from './pages/LoginPage'
import DashboardPage from './pages/DashboardPage'
import MatchesPage from './pages/MatchesPage'
import LiveMatchPage from './pages/LiveMatchPage'
import StandingsPage from './pages/StandingsPage'
import TeamsPage from './pages/TeamsPage'

function isAuthenticated() {
  return !!localStorage.getItem('gfc_token')
}

function PrivateRoute({ children }: { children: React.ReactNode }) {
  return isAuthenticated() ? <>{children}</> : <Navigate to="/login" replace />
}

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<LoginPage />} />
        <Route path="/" element={<PrivateRoute><Layout /></PrivateRoute>}>
          <Route index element={<Navigate to="/dashboard" replace />} />
          <Route path="dashboard"            element={<DashboardPage />} />
          <Route path="matches"              element={<MatchesPage />} />
          <Route path="matches/:id/live"     element={<LiveMatchPage />} />
          <Route path="standings"            element={<StandingsPage />} />
          <Route path="teams"                element={<TeamsPage />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}
