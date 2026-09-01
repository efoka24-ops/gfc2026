import React from 'react';
import Svg, { Path, Circle, Rect } from 'react-native-svg';
import { colors } from '../theme';

// Jeu d'icônes vectorielles maison — aucun emoji dans l'application.
const PATHS = {
  home:      'M3 10.5 12 3l9 7.5V21H3z|M9.5 21v-6h5v6',
  calendar:  'M8 3v4|M16 3v4|M3 10h18',
  trophy:    'M6 9H4.5a2.5 2.5 0 0 1 0-5H6|M18 9h1.5a2.5 2.5 0 0 0 0-5H18|M6 4h12v5a6 6 0 0 1-12 0z|M12 15v4|M8 21h8',
  shield:    'M12 3l8 3v6c0 5-3.4 8.2-8 9.8C7.4 20.2 4 17 4 12V6z',
  chart:     'M4 20V10|M10 20V4|M16 20v-7|M22 20H2',
  chevron:   'M9 18l6-6-6-6',
  back:      'M15 18l-6-6 6-6',
  bell:      'M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9|M13.7 21a2 2 0 0 1-3.4 0',
  pin:       'M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z',
  clock:     'M12 7v5l3.5 2',
  search:    'M20 20l-4.2-4.2',
  play:      'M8 5.5v13l11-6.5z',
  image:     'M21 16l-5-5-9 9',
  user:      'M4.5 20a7.5 7.5 0 0 1 15 0',
  mail:      'M4 5h16v14H4z|M4 7l8 6 8-6',
  phone:     'M4 4h4l2 5-2.5 1.5a12 12 0 0 0 6 6L15 14l5 2v4a15 15 0 0 1-16-16z',
  star:      'M12 3l2.6 5.6 6.1.8-4.5 4.2 1.2 6-5.4-3-5.4 3 1.2-6L3.3 9.4l6.1-.8z',
  whistle:   'M4 12a5 5 0 1 0 10 0h7l-2 3',
  swap:      'M7 7h10l-3-3|M17 17H7l3 3',
  info:      'M12 11v6|M12 7.5v.5',
};

const EXTRAS = {
  calendar: (c, w) => <Rect key="r" x={3} y={5} width={18} height={16} rx={3} stroke={c} strokeWidth={w} fill="none" />,
  clock:    (c, w) => <Circle key="c" cx={12} cy={12} r={9} stroke={c} strokeWidth={w} fill="none" />,
  search:   (c, w) => <Circle key="c" cx={11} cy={11} r={7} stroke={c} strokeWidth={w} fill="none" />,
  image:    (c, w) => [
    <Rect key="r" x={3} y={3} width={18} height={18} rx={3} stroke={c} strokeWidth={w} fill="none" />,
    <Circle key="c" cx={8.5} cy={9} r={1.8} stroke={c} strokeWidth={w} fill="none" />,
  ],
  user:     (c, w) => <Circle key="c" cx={12} cy={8} r={3.6} stroke={c} strokeWidth={w} fill="none" />,
  info:     (c, w) => <Circle key="c" cx={12} cy={12} r={9} stroke={c} strokeWidth={w} fill="none" />,
  whistle:  (c, w) => <Circle key="c" cx={9} cy={12} r={5} stroke={c} strokeWidth={w} fill="none" />,
};

export default function Icon({ name, size = 20, color = colors.bordeaux, strokeWidth = 2, filled = false }) {
  const spec = PATHS[name];
  if (!spec) return null;
  return (
    <Svg width={size} height={size} viewBox="0 0 24 24">
      {EXTRAS[name] ? EXTRAS[name](color, strokeWidth) : null}
      {spec.split('|').map((d, i) => (
        <Path
          key={i}
          d={d}
          stroke={filled ? 'none' : color}
          fill={filled ? color : 'none'}
          strokeWidth={strokeWidth}
          strokeLinecap="round"
          strokeLinejoin="round"
        />
      ))}
    </Svg>
  );
}
