import { useState } from 'react';
import Icon from './Icon.jsx';
import Home from './views/Home.jsx';
import Fixtures from './views/Fixtures.jsx';
import Standings from './views/Standings.jsx';
import Teams from './views/Teams.jsx';

const TABS = [
  { id: 'accueil', label: 'Accueil', icon: 'home', title: 'Garoua Football Challenge', kicker: '6e édition', C: Home },
  { id: 'matchs', label: 'Matchs', icon: 'calendar', title: 'Calendrier', kicker: 'Toutes compétitions', C: Fixtures },
  { id: 'classement', label: 'Classement', icon: 'trophy', title: 'Classement', kicker: 'Championnat', C: Standings },
  { id: 'equipes', label: 'Équipes', icon: 'shield', title: 'Équipes', kicker: '10 équipes engagées', C: Teams },
];

export default function App() {
  const [tab, setTab] = useState('accueil');
  const cur = TABS.find((t) => t.id === tab) || TABS[0];
  const View = cur.C;
  return (
    <div className="app">
      <header className="hd">
        <svg className="brand" viewBox="0 0 48 48" aria-hidden="true">
          <rect width="48" height="48" rx="10" fill="#7A1F30" />
          <circle cx="24" cy="24" r="13" fill="none" stroke="#E8752A" strokeWidth="3" />
          <path d="M24 15l7 5-2.7 8.3h-8.6L17 20z" fill="#FDF4E8" />
        </svg>
        <div style={{ minWidth: 0 }}>
          <div className="kick">{cur.kicker}</div>
          <h1>{cur.title}</h1>
        </div>
      </header>

      <main className="main"><View /></main>

      <nav className="nav">
        <div className="app-inner">
          {TABS.map((t) => (
            <button key={t.id} className={tab === t.id ? 'on' : ''} onClick={() => setTab(t.id)}>
              <Icon name={t.icon} size={21} color={tab === t.id ? '#5A1424' : '#A1928A'} />
              {t.label}
            </button>
          ))}
        </div>
      </nav>
    </div>
  );
}
