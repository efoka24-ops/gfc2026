import { useState } from "react";
import logoImg from "@/imports/1783702209636.png";

// ── Data ──────────────────────────────────────────────────────────────────────

// Icones SVG (remplacent les emojis — charte GFC, aucun emoji)
function Ico({ n, s = 16 }: { n: string; s?: number }) {
  const paths: Record<string, string> = {
    trophy: "M7 4h10v4a5 5 0 0 1-10 0zM7 6H5a2 2 0 0 0 0 4h2M17 6h2a2 2 0 0 1 0 4h-2M9 20h6M12 13v7",
    ball: "M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18zM12 8l4 3-1.5 4.5h-5L8 11z",
    medal: "M12 15a5 5 0 1 0 0-10 5 5 0 0 0 0 10zM8.2 13.5L6 21l6-3 6 3-2.2-7.5",
    target: "M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18zM12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z",
    pin: "M12 21s7-6 7-11a7 7 0 0 0-14 0c0 5 7 11 7 11zM12 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4z",
    phone: "M4 4h4l2 5-2.5 1.5a12 12 0 0 0 6 6L15 14l5 2v4a15 15 0 0 1-16-16z",
    mail: "M4 5h16v14H4zM4 7l8 6 8-6",
  };
  const d = paths[n] || paths.trophy;
  return (
    <svg width={s} height={s} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" style={{ display: "inline-block", verticalAlign: "-2px", flexShrink: 0 }}>
      {d.split("M").filter(Boolean).map((seg, i) => <path key={i} d={"M" + seg} />)}
    </svg>
  );
}

const TEAMS = [
  {
    id: 1, name: "Étoile du Nord", short: "EDN", tier: "top",
    pts: 16, j: 6, g: 5, n: 1, p: 0, bp: 14, bc: 4,
    coach: "Ibrahim Moussa", founded: 2021, city: "Garoua",
    colors: ["#e8720c", "#1a0c0e"],
    players: ["Adamou Maïga", "Hamidou Yaya", "Barka Ali", "Oumar Sali", "Mahamat Djidda"],
    trophies: ["Champion 2023", "Grand Prix 2024"],
    desc: "L'une des équipes les plus titrées du GFC, connue pour son pressing intense et ses attaquants rapides.",
  },
  {
    id: 2, name: "Lions de Garoua", short: "LDG", tier: "top",
    pts: 13, j: 6, g: 4, n: 1, p: 1, bp: 11, bc: 6,
    coach: "Alioum Boukar", founded: 2020, city: "Garoua",
    colors: ["#7a1c2a", "#e8720c"],
    players: ["Bello Hamadou", "Saidou Njoya", "Issa Ngaroua", "Vidal Mbaye", "Arouna Diko"],
    trophies: ["Champion 2022", "Super Coupe 2023"],
    desc: "Fondateurs du GFC et vainqueurs de la première édition. Un club emblématique avec une forte identité.",
  },
  {
    id: 3, name: "FC Bénoué", short: "FCB", tier: "mid",
    pts: 11, j: 6, g: 3, n: 2, p: 1, bp: 9, bc: 7,
    coach: "Christophe Nana", founded: 2021, city: "Garoua",
    colors: ["#1565C0", "#ffffff"],
    players: ["Moussa Djibrine", "Patrick Nkolo", "Ahmed Garba", "Junior Tchoupo", "Raoul Mengue"],
    trophies: [],
    desc: "Une équipe technique qui s'appuie sur un jeu de possession. Finaliste du Grand Prix 2025.",
  },
  {
    id: 4, name: "Diamants FC", short: "DFC", tier: "mid",
    pts: 10, j: 6, g: 3, n: 1, p: 2, bp: 8, bc: 8,
    coach: "Théodore Wabo", founded: 2022, city: "Garoua",
    colors: ["#2E7D32", "#f5f5f5"],
    players: ["Youssouf Issa", "Franck Momo", "Serge Ngono", "David Ateba", "Boris Kana"],
    trophies: ["GP Mbaïrobé 2023"],
    desc: "Révélation de l'édition 2022, les Diamants misent sur la jeunesse et le collectif.",
  },
  {
    id: 5, name: "Tornado Ngaoundéré", short: "TNG", tier: "mid",
    pts: 8, j: 6, g: 2, n: 2, p: 2, bp: 7, bc: 9,
    coach: "Salif Ouedraogo", founded: 2023, city: "Ngaoundéré",
    colors: ["#6A1B9A", "#ffffff"],
    players: ["Ali Mbodj", "Kalil Hamza", "Cheick Diallo", "Eric Tchibozo", "Samuel Mvogo"],
    trophies: [],
    desc: "Représentant de Ngaoundéré, le Tornado apporte une rivalité régionale passionnante au tournoi.",
  },
  {
    id: 6, name: "United Pitoa", short: "UPT", tier: "low",
    pts: 7, j: 6, g: 2, n: 1, p: 3, bp: 6, bc: 10,
    coach: "Darius Kamga", founded: 2022, city: "Pitoa",
    colors: ["#E65100", "#1a1a1a"],
    players: ["Jules Mfou", "Romain Biya", "Steve Nkoa", "Hervé Tabi", "Cédric Eba"],
    trophies: [],
    desc: "Club de la ville de Pitoa, United apporte la diversité géographique et une défense solide.",
  },
  {
    id: 7, name: "AS Guider", short: "ASG", tier: "low",
    pts: 6, j: 6, g: 1, n: 3, p: 2, bp: 5, bc: 8,
    coach: "Moïse Noubissie", founded: 2023, city: "Guider",
    colors: ["#01579B", "#FDD835"],
    players: ["Gilles Tsafack", "Bruno Amvela", "Thierry Njike", "Léon Abah", "Simon Nkolo"],
    trophies: [],
    desc: "Néophyte courageux du GFC, l'AS Guider progresse édition après édition avec beaucoup d'ambition.",
  },
  {
    id: 8, name: "FC Figuil", short: "FCF", tier: "low",
    pts: 4, j: 6, g: 1, n: 1, p: 4, bp: 4, bc: 12,
    coach: "Etienne Dinga", founded: 2024, city: "Figuil",
    colors: ["#880E4F", "#ffffff"],
    players: ["Marcel Ekoa", "Roméo Akoa", "Antoine Bekolo", "Raoul Ondoua", "Luc Abessolo"],
    trophies: [],
    desc: "Nouvelle recrue du GFC, FC Figuil découvre la compétition et forge son caractère dès cette édition.",
  },
  {
    id: 9, name: "Nomades FC", short: "NFC", tier: "low",
    pts: 3, j: 6, g: 0, n: 3, p: 3, bp: 3, bc: 11,
    coach: "Pascal Enow", founded: 2023, city: "Garoua",
    colors: ["#37474F", "#CFD8DC"],
    players: ["Alexis Mbe", "Norbert Ayissi", "Constant Etoundi", "Gaston Mbem", "Lionel Ndi"],
    trophies: [],
    desc: "Une équipe au style nomade, toujours en quête d'identité et de résultats positifs.",
  },
  {
    id: 10, name: "Racing Garoua", short: "RCG", tier: "low",
    pts: 1, j: 6, g: 0, n: 1, p: 5, bp: 2, bc: 14,
    coach: "Ferdinand Nlend", founded: 2024, city: "Garoua",
    colors: ["#BF360C", "#FFEB3B"],
    players: ["William Banga", "Oscar Eto", "Fabrice Nkoa", "Philippe Mveng", "Claude Owono"],
    trophies: [],
    desc: "Dernier au classement mais premier dans le cœur des supporters du quartier. Le Racing ne lâche jamais.",
  },
];

const MATCHES = [
  { id: 1, date: "Sam 2 Août", time: "16:00", home: "Étoile du Nord", away: "Lions de Garoua", homeScore: 2, awayScore: 1, status: "done", competition: "Grand Prix Mbaïrobé" },
  { id: 2, date: "Sam 2 Août", time: "18:30", home: "FC Bénoué", away: "Diamants FC", homeScore: 1, awayScore: 1, status: "live", competition: "Championnat" },
  { id: 3, date: "Dim 3 Août", time: "16:00", home: "Tornado Ngaoundéré", away: "United Pitoa", homeScore: null, awayScore: null, status: "upcoming", competition: "Championnat" },
  { id: 4, date: "Dim 3 Août", time: "18:30", home: "Lions de Garoua", away: "AS Guider", homeScore: null, awayScore: null, status: "upcoming", competition: "Grand Prix Mbaïrobé" },
  { id: 5, date: "Lun 4 Août", time: "17:00", home: "Racing Garoua", away: "FC Figuil", homeScore: null, awayScore: null, status: "upcoming", competition: "Super Coupe" },
];

const PLAYERS = [
  { id: 1, name: "Adamou Maïga", team: "Étoile du Nord", pos: "ATT", goals: 7, assists: 3, img: "photo-1519032284022-0fdfbdb3c42e" },
  { id: 2, name: "Bello Hamadou", team: "Lions de Garoua", pos: "MIL", goals: 4, assists: 6, img: "photo-1652665314612-c48e10a01598" },
  { id: 3, name: "Moussa Djibrine", team: "FC Bénoué", pos: "ATT", goals: 5, assists: 2, img: "photo-1722978687695-212eecfa4cbe" },
  { id: 4, name: "Youssouf Issa", team: "Diamants FC", pos: "DEF", goals: 1, assists: 4, img: "photo-1519032284022-0fdfbdb3c42e" },
];

// ── Team color helper ─────────────────────────────────────────────────────────

function teamColorBg(team: typeof TEAMS[0]) {
  return team.colors[0];
}

// ── Team Drawer ───────────────────────────────────────────────────────────────

function TeamDrawer({ team, onClose }: { team: typeof TEAMS[0]; onClose: () => void }) {
  const tierLabel = team.tier === "top" ? "Zone Qualif." : team.tier === "mid" ? "Milieu" : "Bas de tableau";
  const tierColor = team.tier === "top" ? "#e8720c" : team.tier === "mid" ? "#7a1c2a" : "#888";

  return (
    <>
      {/* Backdrop */}
      <div
        className="overlay-enter fixed inset-0 z-40"
        style={{ background: "rgba(26,12,14,0.55)", backdropFilter: "blur(2px)" }}
        onClick={onClose}
      />
      {/* Drawer */}
      <div
        className="drawer-enter fixed left-0 top-0 bottom-0 z-50 flex flex-col overflow-hidden"
        style={{ width: "85%", maxWidth: 340, background: "var(--card)", boxShadow: "4px 0 32px rgba(122,28,42,0.18)" }}
      >
        {/* Header - team banner */}
        <div
          className="relative px-5 pt-10 pb-6 flex-shrink-0"
          style={{ background: `linear-gradient(135deg, ${team.colors[0]} 0%, ${team.colors[0]}cc 100%)` }}
        >
          <button
            onClick={onClose}
            className="absolute top-4 right-4 w-8 h-8 rounded-full flex items-center justify-center"
            style={{ background: "rgba(255,255,255,0.2)" }}
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="2.5">
              <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
          {/* Shield emblem */}
          <div className="w-16 h-16 rounded-2xl flex items-center justify-center mb-3" style={{ background: "rgba(255,255,255,0.2)" }}>
            <span className="font-hero text-3xl text-white">{team.short}</span>
          </div>
          <h2 className="font-display font-bold text-xl text-white leading-tight">{team.name}</h2>
          <p className="text-sm text-white/70 mt-0.5">{team.city} · Fondé en {team.founded}</p>
          <div className="flex items-center gap-2 mt-2">
            <span className="text-xs font-display font-semibold px-2 py-0.5 rounded-full" style={{ background: "rgba(255,255,255,0.2)", color: "#fff" }}>
              {tierLabel}
            </span>
            {team.trophies.length > 0 && (
              <span className="text-xs font-display font-semibold px-2 py-0.5 rounded-full" style={{ background: "rgba(232,114,12,0.8)", color: "#fff" }}>
                <Ico n="trophy" s={13} /> {team.trophies.length} trophée{team.trophies.length > 1 ? "s" : ""}
              </span>
            )}
          </div>
        </div>

        {/* Scrollable content */}
        <div className="flex-1 overflow-y-auto">
          {/* Stats row */}
          <div className="grid grid-cols-4 gap-0 border-b" style={{ borderColor: "var(--border)" }}>
            {[
              { label: "Pts", value: team.pts, accent: true },
              { label: "V", value: team.g },
              { label: "N", value: team.n },
              { label: "D", value: team.p },
            ].map((s) => (
              <div key={s.label} className="py-4 text-center border-r last:border-r-0" style={{ borderColor: "var(--border)" }}>
                <p className="font-hero text-2xl" style={{ color: s.accent ? "var(--accent)" : "var(--foreground)" }}>{s.value}</p>
                <p className="text-[10px] font-display font-semibold uppercase" style={{ color: "var(--muted-foreground)" }}>{s.label}</p>
              </div>
            ))}
          </div>

          <div className="px-5 py-4 space-y-5">
            {/* Description */}
            <div>
              <h3 className="font-display font-bold text-xs uppercase tracking-widest mb-2" style={{ color: "var(--muted-foreground)" }}>Présentation</h3>
              <p className="text-sm leading-relaxed" style={{ color: "var(--foreground)" }}>{team.desc}</p>
            </div>

            {/* Coach */}
            <div>
              <h3 className="font-display font-bold text-xs uppercase tracking-widest mb-2" style={{ color: "var(--muted-foreground)" }}>Entraîneur</h3>
              <div className="flex items-center gap-3 rounded-xl p-3" style={{ background: "var(--secondary)" }}>
                <div className="w-9 h-9 rounded-full flex items-center justify-center" style={{ background: teamColorBg(team) }}>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="2">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                </div>
                <div>
                  <p className="font-display font-semibold text-sm" style={{ color: "var(--foreground)" }}>{team.coach}</p>
                  <p className="text-xs" style={{ color: "var(--muted-foreground)" }}>Coach principal</p>
                </div>
              </div>
            </div>

            {/* Buts */}
            <div>
              <h3 className="font-display font-bold text-xs uppercase tracking-widest mb-2" style={{ color: "var(--muted-foreground)" }}>Bilan offensif</h3>
              <div className="flex gap-3">
                <div className="flex-1 rounded-xl p-3 text-center" style={{ background: "var(--secondary)" }}>
                  <p className="font-hero text-2xl" style={{ color: "var(--accent)" }}>{team.bp}</p>
                  <p className="text-[10px]" style={{ color: "var(--muted-foreground)" }}>Buts marqués</p>
                </div>
                <div className="flex-1 rounded-xl p-3 text-center" style={{ background: "var(--secondary)" }}>
                  <p className="font-hero text-2xl" style={{ color: "var(--primary)" }}>{team.bc}</p>
                  <p className="text-[10px]" style={{ color: "var(--muted-foreground)" }}>Buts encaissés</p>
                </div>
                <div className="flex-1 rounded-xl p-3 text-center" style={{ background: "var(--secondary)" }}>
                  <p className="font-hero text-2xl" style={{ color: team.bp - team.bc >= 0 ? "#2E7D32" : "#C62828" }}>
                    {team.bp - team.bc > 0 ? "+" : ""}{team.bp - team.bc}
                  </p>
                  <p className="text-[10px]" style={{ color: "var(--muted-foreground)" }}>Diff.</p>
                </div>
              </div>
            </div>

            {/* Players */}
            <div>
              <h3 className="font-display font-bold text-xs uppercase tracking-widest mb-2" style={{ color: "var(--muted-foreground)" }}>Effectif clé</h3>
              <div className="space-y-2">
                {team.players.map((p, i) => (
                  <div key={p} className="flex items-center gap-3 rounded-xl px-3 py-2.5" style={{ background: "var(--secondary)" }}>
                    <span className="font-hero text-sm w-5 text-center" style={{ color: "var(--muted-foreground)" }}>{i + 1}</span>
                    <div className="w-7 h-7 rounded-full flex items-center justify-center text-white text-[11px] font-display font-bold flex-shrink-0" style={{ background: teamColorBg(team) }}>
                      {p.split(" ").map(w => w[0]).join("").slice(0, 2)}
                    </div>
                    <span className="text-sm font-medium" style={{ color: "var(--foreground)" }}>{p}</span>
                  </div>
                ))}
              </div>
            </div>

            {/* Trophies */}
            {team.trophies.length > 0 && (
              <div>
                <h3 className="font-display font-bold text-xs uppercase tracking-widest mb-2" style={{ color: "var(--muted-foreground)" }}>Palmarès</h3>
                <div className="space-y-2">
                  {team.trophies.map((t) => (
                    <div key={t} className="flex items-center gap-2 text-sm" style={{ color: "var(--foreground)" }}>
                      <span><Ico n="trophy" s={14} /></span>
                      <span>{t}</span>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Colors */}
            <div>
              <h3 className="font-display font-bold text-xs uppercase tracking-widest mb-2" style={{ color: "var(--muted-foreground)" }}>Couleurs</h3>
              <div className="flex gap-2">
                {team.colors.map((c) => (
                  <div key={c} className="w-8 h-8 rounded-full border-2 border-white/30" style={{ background: c, boxShadow: "0 1px 4px rgba(0,0,0,0.2)" }} />
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}

// ── Sub-components ─────────────────────────────────────────────────────────────

function LiveBadge() {
  return (
    <span className="flex items-center gap-1 bg-red-600 text-white text-[10px] font-display font-semibold px-2 py-0.5 rounded-full uppercase tracking-wider">
      <span className="w-1.5 h-1.5 rounded-full bg-white live-dot inline-block" />
      Live
    </span>
  );
}

function MatchCard({ match }: { match: typeof MATCHES[0] }) {
  const isLive = match.status === "live";
  const isDone = match.status === "done";
  return (
    <div
      className={`match-card rounded-xl p-4 mb-3 transition-all border ${isLive ? "orange-glow" : ""}`}
      style={{
        background: isLive ? "#fff8f2" : "var(--card)",
        borderColor: isLive ? "rgba(232,114,12,0.5)" : "var(--border)",
      }}
    >
      <div className="flex items-center justify-between mb-3">
        <span className="text-[11px] font-medium" style={{ color: "var(--muted-foreground)" }}>{match.competition}</span>
        {isLive ? <LiveBadge /> : (
          <span className="text-[11px]" style={{ color: "var(--muted-foreground)" }}>{match.date} · {match.time}</span>
        )}
      </div>
      <div className="flex items-center justify-between gap-2">
        <p className="flex-1 text-right font-display font-semibold text-sm" style={{ color: "var(--foreground)" }}>{match.home}</p>
        <div className="flex items-center gap-2 px-3">
          {isDone || isLive ? (
            <span className="font-hero text-2xl tracking-wider" style={{ color: isLive ? "var(--accent)" : "var(--primary)" }}>
              {match.homeScore} <span className="text-lg opacity-40">-</span> {match.awayScore}
            </span>
          ) : (
            <span className="font-display text-sm" style={{ color: "var(--muted-foreground)" }}>vs</span>
          )}
        </div>
        <p className="flex-1 font-display font-semibold text-sm" style={{ color: "var(--foreground)" }}>{match.away}</p>
      </div>
    </div>
  );
}

function PlayerCard({ player }: { player: typeof PLAYERS[0] }) {
  return (
    <div className="rounded-xl overflow-hidden border" style={{ background: "var(--card)", borderColor: "var(--border)" }}>
      <div className="relative h-40 bg-gray-200">
        <img
          src={`https://images.unsplash.com/${player.img}?w=300&h=300&fit=crop&auto=format`}
          alt={player.name}
          className="w-full h-full object-cover object-top"
        />
        <div className="absolute inset-0" style={{ background: "linear-gradient(to top, rgba(122,28,42,0.85) 0%, transparent 55%)" }} />
        <span className="absolute bottom-2 left-3 text-[10px] font-display font-bold px-2 py-0.5 rounded-full text-white" style={{ background: "var(--accent)" }}>{player.pos}</span>
      </div>
      <div className="p-3">
        <p className="font-display font-bold text-sm" style={{ color: "var(--foreground)" }}>{player.name}</p>
        <p className="text-[11px] mb-3" style={{ color: "var(--muted-foreground)" }}>{player.team}</p>
        <div className="flex gap-4">
          <div>
            <p className="font-hero text-xl" style={{ color: "var(--accent)" }}>{player.goals}</p>
            <p className="text-[10px]" style={{ color: "var(--muted-foreground)" }}>Buts</p>
          </div>
          <div>
            <p className="font-hero text-xl" style={{ color: "var(--primary)" }}>{player.assists}</p>
            <p className="text-[10px]" style={{ color: "var(--muted-foreground)" }}>Passes D.</p>
          </div>
        </div>
      </div>
    </div>
  );
}

// ── Pages ─────────────────────────────────────────────────────────────────────

function HomePage({ setPage }: { setPage: (p: string) => void }) {
  const liveMatch = MATCHES.find((m) => m.status === "live");
  const upcoming = MATCHES.filter((m) => m.status === "upcoming").slice(0, 2);

  return (
    <div className="pb-6">
      {/* Hero */}
      <div className="relative h-52 overflow-hidden rounded-b-3xl" style={{ background: "var(--primary)" }}>
        <img
          src="https://images.unsplash.com/photo-1700870748737-c1e533c9e3b4?w=800&h=400&fit=crop&auto=format"
          alt="Match"
          className="absolute inset-0 w-full h-full object-cover opacity-50"
        />
        <div className="hero-overlay absolute inset-0" />
        <div className="relative z-10 px-4 pt-5">
          <div className="flex items-center gap-3 mb-4">
            <img src={logoImg} alt="GFC" className="w-10 h-10 object-contain" />
            <div>
              <p className="font-display text-[11px] tracking-widest uppercase text-orange-300">6e Édition · Since 2020</p>
              <h1 className="font-display text-lg font-bold text-white leading-tight">Garoua Football Challenge</h1>
            </div>
          </div>
          {liveMatch && (
            <div className="rounded-xl p-3 border border-orange-400/40" style={{ background: "rgba(255,255,255,0.12)", backdropFilter: "blur(8px)" }}>
              <div className="flex items-center gap-2 mb-2">
                <LiveBadge />
                <span className="text-[11px] text-white/70">{liveMatch.competition}</span>
              </div>
              <div className="flex items-center justify-between">
                <p className="font-display font-bold text-sm flex-1 text-right text-white">{liveMatch.home}</p>
                <span className="font-hero text-3xl mx-3 text-orange-300">{liveMatch.homeScore} – {liveMatch.awayScore}</span>
                <p className="font-display font-bold text-sm flex-1 text-white">{liveMatch.away}</p>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Quick stats */}
      <div className="px-4 mt-4">
        <div className="grid grid-cols-3 gap-3">
          {[
            { label: "Équipes", value: "10" },
            { label: "Compétitions", value: "3" },
            { label: "Édition", value: "6e" },
          ].map((s) => (
            <div key={s.label} className="rounded-xl p-3 text-center border" style={{ background: "var(--card)", borderColor: "var(--border)" }}>
              <p className="font-hero text-2xl" style={{ color: "var(--accent)" }}>{s.value}</p>
              <p className="text-[11px]" style={{ color: "var(--muted-foreground)" }}>{s.label}</p>
            </div>
          ))}
        </div>
      </div>

      {/* Competitions */}
      <div className="px-4 mt-5">
        <h2 className="font-display font-bold text-sm uppercase tracking-widest mb-3" style={{ color: "var(--muted-foreground)" }}>Compétitions</h2>
        <div className="space-y-2">
          {[
            { icon: "trophy", name: "Grand Prix Gabriel Mbaïrobé", sub: "10 équipes · Tournoi principal", color: "#e8720c" },
            { icon: "ball", name: "Championnat de Vacances", sub: "10 équipes · Poules + Playoffs", color: "#7a1c2a" },
            { icon: "medal", name: "Super Coupe", sub: "4 équipes · Finale des finalistes", color: "#9e7820" },
          ].map((c) => (
            <div key={c.name} className="flex items-center gap-3 rounded-xl p-3 border" style={{ background: "var(--card)", borderColor: "var(--border)" }}>
              <div className="w-9 h-9 rounded-lg flex items-center justify-center text-xl" style={{ background: c.color + "18" }}><Ico n={c.icon} s={20} /></div>
              <div className="flex-1">
                <p className="font-display font-semibold text-sm" style={{ color: "var(--foreground)" }}>{c.name}</p>
                <p className="text-[11px]" style={{ color: "var(--muted-foreground)" }}>{c.sub}</p>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Upcoming */}
      <div className="px-4 mt-5">
        <div className="flex items-center justify-between mb-3">
          <h2 className="font-display font-bold text-sm uppercase tracking-widest" style={{ color: "var(--muted-foreground)" }}>Prochains Matchs</h2>
          <button onClick={() => setPage("matches")} className="text-xs font-display font-semibold" style={{ color: "var(--accent)" }}>Voir tout →</button>
        </div>
        {upcoming.map((m) => <MatchCard key={m.id} match={m} />)}
      </div>

      {/* Top scorer */}
      <div className="px-4 mt-5">
        <h2 className="font-display font-bold text-sm uppercase tracking-widest mb-3" style={{ color: "var(--muted-foreground)" }}>Meilleur Buteur</h2>
        <div className="rounded-xl overflow-hidden border flex items-center gap-4 p-4" style={{ background: "var(--card)", borderColor: "var(--border)" }}>
          <div className="w-14 h-14 rounded-full overflow-hidden flex-shrink-0 border-2" style={{ borderColor: "var(--accent)" }}>
            <img src="https://images.unsplash.com/photo-1519032284022-0fdfbdb3c42e?w=100&h=100&fit=crop&auto=format" alt="Adamou Maïga" className="w-full h-full object-cover object-top" />
          </div>
          <div className="flex-1">
            <p className="font-display font-bold text-sm" style={{ color: "var(--foreground)" }}>Adamou Maïga</p>
            <p className="text-[11px] mb-2" style={{ color: "var(--muted-foreground)" }}>Étoile du Nord · ATT</p>
            <div className="flex items-center gap-2">
              <div className="flex-1 h-1.5 rounded-full" style={{ background: "var(--muted)" }}>
                <div className="h-full rounded-full" style={{ width: "100%", background: "var(--accent)" }} />
              </div>
              <span className="font-hero text-lg ml-1" style={{ color: "var(--accent)" }}>7 buts</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function MatchesPage() {
  const [filter, setFilter] = useState<"all" | "live" | "upcoming" | "done">("all");
  const tabs = [
    { key: "all", label: "Tous" },
    { key: "live", label: "En Direct" },
    { key: "upcoming", label: "À Venir" },
    { key: "done", label: "Résultats" },
  ] as const;
  const filtered = filter === "all" ? MATCHES : MATCHES.filter((m) => m.status === filter);

  return (
    <div className="pb-6">
      <div className="px-4 pt-2 pb-4 border-b" style={{ borderColor: "var(--border)" }}>
        <h1 className="font-display font-bold text-xl uppercase tracking-wide mb-3" style={{ color: "var(--foreground)" }}>Matchs</h1>
        <div className="flex gap-1.5 overflow-x-auto pb-1">
          {tabs.map((t) => (
            <button
              key={t.key}
              onClick={() => setFilter(t.key)}
              className="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-display font-semibold transition-all"
              style={filter === t.key ? { background: "var(--accent)", color: "#fff" } : { background: "var(--muted)", color: "var(--muted-foreground)" }}
            >
              {t.label}
            </button>
          ))}
        </div>
      </div>
      <div className="px-4 mt-4">
        {filtered.length === 0
          ? <p className="text-center py-10 text-sm" style={{ color: "var(--muted-foreground)" }}>Aucun match dans cette catégorie</p>
          : filtered.map((m) => <MatchCard key={m.id} match={m} />)}
      </div>
    </div>
  );
}

function StandingsPage({ onTeamClick }: { onTeamClick: (t: typeof TEAMS[0]) => void }) {
  return (
    <div className="pb-6">
      <div className="px-4 pt-2 pb-4 border-b" style={{ borderColor: "var(--border)" }}>
        <h1 className="font-display font-bold text-xl uppercase tracking-wide" style={{ color: "var(--foreground)" }}>Classement</h1>
        <p className="text-xs mt-0.5" style={{ color: "var(--muted-foreground)" }}>Appuyez sur une équipe pour voir les détails</p>
      </div>
      <div className="px-4 mt-3">
        <div className="flex items-center px-2 py-1 mb-1">
          <span className="w-6 text-[10px] text-center" style={{ color: "var(--muted-foreground)" }}>#</span>
          <span className="flex-1 ml-3 text-[10px]" style={{ color: "var(--muted-foreground)" }}>Équipe</span>
          {["J", "G", "N", "P", "Pts"].map((h) => (
            <span key={h} className="w-7 text-[10px] text-center" style={{ color: "var(--muted-foreground)" }}>{h}</span>
          ))}
        </div>
        {TEAMS.map((team, i) => (
          <button
            key={team.id}
            onClick={() => onTeamClick(team)}
            className="w-full flex items-center px-2 py-3 rounded-xl mb-1 border text-left active:scale-[0.99] transition-transform"
            style={{
              background: i < 3 ? "rgba(122,28,42,0.06)" : "var(--card)",
              borderColor: i < 3 ? "rgba(232,114,12,0.25)" : "var(--border)",
            }}
          >
            <span className="w-6 text-center font-display font-bold text-xs" style={{ color: i < 3 ? "var(--accent)" : "var(--muted-foreground)" }}>{i + 1}</span>
            <div className="flex-1 ml-3 flex items-center gap-2">
              <div className="w-6 h-6 rounded-full flex items-center justify-center text-[9px] font-display font-bold text-white flex-shrink-0" style={{ background: team.colors[0] }}>
                {team.short[0]}
              </div>
              <span className="font-display font-semibold text-xs truncate" style={{ color: "var(--foreground)" }}>{team.name}</span>
            </div>
            {[team.j, team.g, team.n, team.p].map((v, vi) => (
              <span key={vi} className="w-7 text-center text-xs" style={{ color: "var(--muted-foreground)" }}>{v}</span>
            ))}
            <span className="w-7 text-center font-display font-bold text-xs" style={{ color: i < 3 ? "var(--accent)" : "var(--foreground)" }}>{team.pts}</span>
          </button>
        ))}
        <p className="text-[10px] mt-3 flex items-center gap-1.5" style={{ color: "var(--muted-foreground)" }}>
          <span className="inline-block w-3 h-3 rounded border" style={{ background: "rgba(122,28,42,0.06)", borderColor: "rgba(232,114,12,0.25)" }} />
          Zone de qualification
        </p>
      </div>
    </div>
  );
}

function PlayersPage() {
  return (
    <div className="pb-6">
      <div className="px-4 pt-2 pb-4 border-b" style={{ borderColor: "var(--border)" }}>
        <h1 className="font-display font-bold text-xl uppercase tracking-wide" style={{ color: "var(--foreground)" }}>Joueurs</h1>
        <p className="text-xs mt-0.5" style={{ color: "var(--muted-foreground)" }}>Statistiques · Édition 2026</p>
      </div>
      {/* Top scorer banner */}
      <div className="mx-4 mt-4 mb-5 rounded-2xl overflow-hidden relative" style={{ background: "var(--primary)" }}>
        <img
          src="https://images.unsplash.com/photo-1652665314612-c48e10a01598?w=800&h=280&fit=crop&auto=format"
          alt="Meilleur buteur"
          className="absolute inset-0 w-full h-full object-cover opacity-30"
        />
        <div className="relative z-10 p-5">
          <p className="font-display text-[11px] tracking-widest uppercase mb-1 text-orange-300"><Ico n="medal" s={12} /> Meilleur Buteur</p>
          <h2 className="font-hero text-3xl text-white mb-0.5">Adamou Maïga</h2>
          <p className="text-xs text-white/60 mb-3">Étoile du Nord · Attaquant</p>
          <div className="flex gap-5">
            {[{ v: "7", l: "Buts" }, { v: "3", l: "Passes D." }, { v: "6", l: "Matchs" }].map(s => (
              <div key={s.l}>
                <p className="font-hero text-3xl" style={{ color: s.l === "Buts" ? "#fdba74" : "#fff" }}>{s.v}</p>
                <p className="text-[11px] text-white/60">{s.l}</p>
              </div>
            ))}
          </div>
        </div>
      </div>
      <div className="px-4">
        <h3 className="font-display font-bold text-sm uppercase tracking-widest mb-3" style={{ color: "var(--muted-foreground)" }}>Classement</h3>
        <div className="grid grid-cols-2 gap-3">
          {PLAYERS.map((p) => <PlayerCard key={p.id} player={p} />)}
        </div>
      </div>
    </div>
  );
}

function TeamsPage({ onTeamClick }: { onTeamClick: (t: typeof TEAMS[0]) => void }) {
  return (
    <div className="pb-6">
      <div className="px-4 pt-2 pb-4 border-b" style={{ borderColor: "var(--border)" }}>
        <h1 className="font-display font-bold text-xl uppercase tracking-wide" style={{ color: "var(--foreground)" }}>Équipes</h1>
        <p className="text-xs mt-0.5" style={{ color: "var(--muted-foreground)" }}>10 équipes · Édition 2026 — Appuyez pour les détails</p>
      </div>
      <div className="px-4 mt-4 grid grid-cols-1 gap-3">
        {TEAMS.map((team, i) => (
          <button
            key={team.id}
            onClick={() => onTeamClick(team)}
            className="w-full text-left rounded-2xl border overflow-hidden active:scale-[0.99] transition-transform"
            style={{ background: "var(--card)", borderColor: "var(--border)" }}
          >
            {/* Color strip */}
            <div className="h-1.5" style={{ background: `linear-gradient(90deg, ${team.colors[0]}, ${team.colors[1]})` }} />
            <div className="flex items-center gap-3 px-4 py-3">
              {/* Rank */}
              <span className="font-hero text-3xl w-8 text-center flex-shrink-0" style={{ color: i < 3 ? "var(--accent)" : "var(--muted-foreground)" }}>
                {i + 1}
              </span>
              {/* Shield */}
              <div className="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style={{ background: team.colors[0] + "22" }}>
                <span className="font-hero text-lg" style={{ color: team.colors[0] }}>{team.short}</span>
              </div>
              <div className="flex-1 min-w-0">
                <p className="font-display font-bold text-sm truncate" style={{ color: "var(--foreground)" }}>{team.name}</p>
                <p className="text-[11px] truncate" style={{ color: "var(--muted-foreground)" }}>{team.city} · Coach: {team.coach}</p>
                <div className="flex items-center gap-3 mt-1">
                  <span className="text-xs font-display font-bold" style={{ color: "var(--accent)" }}>{team.pts} pts</span>
                  <span className="text-[11px]" style={{ color: "var(--muted-foreground)" }}>{team.g}V {team.n}N {team.p}D</span>
                  {team.trophies.length > 0 && <span className="text-[11px]" style={{ display: "inline-flex", alignItems: "center", gap: 3 }}><Ico n="trophy" s={11} /> {team.trophies.length}</span>}
                </div>
              </div>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" style={{ color: "var(--muted-foreground)", flexShrink: 0 }}>
                <polyline points="9 18 15 12 9 6" />
              </svg>
            </div>
          </button>
        ))}
      </div>
    </div>
  );
}

function AboutPage() {
  return (
    <div className="pb-6">
      <div className="relative px-5 pt-8 pb-10 rounded-b-3xl text-center overflow-hidden" style={{ background: "var(--primary)" }}>
        <img
          src="https://images.unsplash.com/photo-1686947079063-f1e7a7dfc6a9?w=800&h=400&fit=crop&auto=format"
          alt="Stade"
          className="absolute inset-0 w-full h-full object-cover opacity-25"
        />
        <div className="relative z-10">
          <img src={logoImg} alt="GFC" className="w-24 h-24 object-contain mx-auto mb-3" />
          <h1 className="font-hero text-4xl text-white">GAROUA</h1>
          <h2 className="font-display font-bold text-lg tracking-widest text-orange-300">FOOTBALL CHALLENGE</h2>
          <p className="text-sm mt-1 text-white/60">Since 2020 · 6e Édition</p>
        </div>
      </div>
      <div className="px-4 mt-5 space-y-4">
        <div className="rounded-xl p-4 border" style={{ background: "var(--card)", borderColor: "var(--border)" }}>
          <h3 className="font-display font-bold text-xs uppercase tracking-widest mb-2" style={{ color: "var(--accent)" }}><Ico n="target" s={13} /> Notre Mission</h3>
          <p className="text-sm leading-relaxed" style={{ color: "var(--foreground)" }}>
            Vulgariser les talents, mettre en avant le professionnalisme et permettre aux jeunes footballeurs de Garoua d'évoluer dans un milieu professionnel pendant les vacances.
          </p>
        </div>
        <div className="rounded-xl p-4 border" style={{ background: "var(--card)", borderColor: "rgba(232,114,12,0.2)" }}>
          <h3 className="font-display font-bold text-xs uppercase tracking-widest mb-2" style={{ color: "var(--accent)" }}><Ico n="trophy" s={13} /> Grand Prix Gabriel Mbaïrobé</h3>
          <p className="text-sm leading-relaxed" style={{ color: "var(--foreground)" }}>
            Le tournoi phare du GFC, dédié au fondateur Gabriel Mbaïrobé. Il réunit les meilleures équipes dans un format compétitif élitiste visant l'excellence.
          </p>
        </div>
        <div className="rounded-xl p-4 border" style={{ background: "var(--card)", borderColor: "var(--border)" }}>
          <h3 className="font-display font-bold text-xs uppercase tracking-widest mb-2" style={{ color: "var(--primary)" }}><Ico n="medal" s={13} /> Super Coupe</h3>
          <p className="text-sm leading-relaxed" style={{ color: "var(--foreground)" }}>
            Le choc des champions. Vainqueur du Championnat vs lauréat du Grand Prix Mbaïrobé pour le titre suprême.
          </p>
        </div>
        <div className="rounded-xl p-4 border" style={{ background: "var(--card)", borderColor: "var(--border)" }}>
          <h3 className="font-display font-bold text-xs uppercase tracking-widest mb-3" style={{ color: "var(--foreground)" }}>Édition 2026 en chiffres</h3>
          <div className="grid grid-cols-3 gap-3 text-center">
            {[{ l: "Équipes", v: "10" }, { l: "Compétitions", v: "3" }, { l: "Villes", v: "2" }].map((s) => (
              <div key={s.l}>
                <p className="font-hero text-3xl" style={{ color: "var(--accent)" }}>{s.v}</p>
                <p className="text-[11px]" style={{ color: "var(--muted-foreground)" }}>{s.l}</p>
              </div>
            ))}
          </div>
        </div>
        <div className="rounded-xl p-4 border" style={{ background: "var(--card)", borderColor: "var(--border)" }}>
          <h3 className="font-display font-bold text-xs uppercase tracking-widest mb-3" style={{ color: "var(--foreground)" }}>Contact & Réseaux</h3>
          <div className="space-y-2">
            {[{ i: "pin", l: "Garoua, Cameroun" }, { i: "phone", l: "+237 6XX XXX XXX" }, { i: "mail", l: "gfc@garoua-fc.cm" }].map((c) => (
              <div key={c.l} className="flex items-center gap-3">
                <span style={{ color: "var(--accent)" }}><Ico n={c.i} s={15} /></span>
                <span className="text-sm" style={{ color: "var(--muted-foreground)" }}>{c.l}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

// ── Nav ───────────────────────────────────────────────────────────────────────

const NAV_ITEMS = [
  { key: "home",      label: "Accueil",    d: "M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z M9 22V12h6v10" },
  { key: "matches",   label: "Matchs",     d: "M8 6l4-4 4 4M8 18l4 4 4-4M4 12h16" },
  { key: "teams",     label: "Équipes",    d: "M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2 M9 7a4 4 0 100 0 4 4 0 000-8zm0 0 M23 21v-2a4 4 0 00-3-3.87 M16 3.13a4 4 0 010 7.75" },
  { key: "standings", label: "Classement", d: "M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z" },
  { key: "about",     label: "À propos",   d: "M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z M12 16v-4 M12 8h.01" },
] as const;

// ── App Shell ─────────────────────────────────────────────────────────────────

export default function App() {
  const [page, setPage] = useState<string>("home");
  const [drawerTeam, setDrawerTeam] = useState<typeof TEAMS[0] | null>(null);

  return (
    <div className="size-full flex flex-col relative" style={{ background: "var(--background)", maxWidth: 430, margin: "0 auto" }}>
      {/* Status bar */}
      <div className="flex items-center justify-between px-5 pt-3 pb-1 flex-shrink-0" style={{ background: "var(--background)" }}>
        <span className="text-xs font-semibold" style={{ color: "var(--foreground)" }}>9:41</span>
        <div className="flex items-center gap-1.5">
          <svg width="15" height="10" viewBox="0 0 15 10" fill="currentColor" style={{ color: "var(--foreground)", opacity: 0.6 }}>
            <rect x="0" y="3" width="2.5" height="7" rx="0.8" />
            <rect x="4" y="2" width="2.5" height="8" rx="0.8" />
            <rect x="8" y="0.5" width="2.5" height="9.5" rx="0.8" />
            <rect x="12" y="0" width="2.5" height="10" rx="0.8" opacity="0.3" />
          </svg>
          <div className="flex items-center gap-0.5">
            <div className="w-5 h-2.5 rounded-sm border flex items-center px-0.5" style={{ borderColor: "var(--foreground)", opacity: 0.6 }}>
              <div className="h-1 rounded-sm flex-1" style={{ background: "var(--foreground)" }} />
            </div>
          </div>
        </div>
      </div>

      {/* Page */}
      <div className="flex-1 overflow-y-auto">
        {page === "home"      && <HomePage setPage={setPage} />}
        {page === "matches"   && <MatchesPage />}
        {page === "teams"     && <TeamsPage onTeamClick={setDrawerTeam} />}
        {page === "standings" && <StandingsPage onTeamClick={setDrawerTeam} />}
        {page === "about"     && <AboutPage />}
      </div>

      {/* Bottom nav */}
      <div className="flex-shrink-0 border-t" style={{ background: "var(--card)", borderColor: "var(--border)" }}>
        <div className="flex items-center">
          {NAV_ITEMS.map((item) => {
            const active = page === item.key;
            return (
              <button
                key={item.key}
                onClick={() => setPage(item.key)}
                className="flex-1 flex flex-col items-center py-2.5 gap-0.5 transition-colors"
                style={{ color: active ? "var(--accent)" : "var(--muted-foreground)" }}
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill={active ? "currentColor" : "none"} stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                  {item.d.split(" M").map((seg, i) => (
                    <path key={i} d={i === 0 ? seg : "M" + seg} />
                  ))}
                </svg>
                <span className="text-[9px] font-display font-semibold tracking-wide">{item.label}</span>
              </button>
            );
          })}
        </div>
        <div className="flex justify-center pb-1.5 pt-0.5">
          <div className="w-24 h-1 rounded-full" style={{ background: "var(--foreground)", opacity: 0.12 }} />
        </div>
      </div>

      {/* Team drawer */}
      {drawerTeam && (
        <div className="absolute inset-0 z-40 overflow-hidden">
          <TeamDrawer team={drawerTeam} onClose={() => setDrawerTeam(null)} />
        </div>
      )}
    </div>
  );
}
