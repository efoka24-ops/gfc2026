// Jeu d'icones vectorielles maison — remplace les emojis de l'ancienne PWA.
// Aucun emoji dans l'application (charte GFC).
const P = {
  home: 'M3 11.5 12 4l9 7.5M5 10v10h14V10',
  calendar: 'M7 3v3M17 3v3M4 8h16M4 6h16v14H4z',
  trophy: 'M7 4h10v4a5 5 0 0 1-10 0zM7 6H5a2 2 0 0 0 0 4h2M17 6h2a2 2 0 0 1 0 4h-2M9 20h6M12 13v7',
  shield: 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z',
  ball: 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18zM12 8l4 3-1.5 4.5h-5L8 11z',
  clock: 'M12 7v5l3 2M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18z',
  pin: 'M12 21s7-6 7-11a7 7 0 0 0-14 0c0 5 7 11 7 11zM12 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4z',
  info: 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18zM12 11v6M12 7.5v.5',
  wifi_off: 'M2 4l20 20M8.5 12.5a5 5 0 0 1 5-1M5 9a10 10 0 0 1 8-2.6M12 20h.01',
};
export default function Icon({ name, size = 22, color = 'currentColor', stroke = 2 }) {
  const d = P[name] || P.info;
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none"
      stroke={color} strokeWidth={stroke} strokeLinecap="round" strokeLinejoin="round"
      aria-hidden="true">
      {d.split('M').filter(Boolean).map((seg, i) => <path key={i} d={'M' + seg} />)}
    </svg>
  );
}
