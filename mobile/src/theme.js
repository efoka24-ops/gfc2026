// Charte issue du logo Garoua Football Challenge
export const colors = {
  bordeaux: '#5A1424',
  bordeauxMid: '#7A1F30',
  bordeauxDeep: '#3E0B18',
  orange: '#E8752A',
  orangeSoft: '#F2A057',
  brick: '#B8452A',
  cream: '#FDF4E8',
  card: '#FFFFFF',
  ink: '#2A1013',
  text: '#4A3A34',
  muted: '#8A7F79',
  faint: '#A1928A',
  line: 'rgba(90,20,36,0.10)',
  green: '#2F7A4A',
  yellow: '#E3B32A',
  red: '#B5453A',
  live: '#FF4D4D',
};

export const fonts = {
  display: 'Anton_400Regular',
  body: 'Manrope_500Medium',
  bodyBold: 'Manrope_700Bold',
  bodyBlack: 'Manrope_800ExtraBold',
};

export const spacing = { xs: 4, sm: 8, md: 12, lg: 16, xl: 22, xxl: 32 };
export const radius = { sm: 8, md: 12, lg: 16, xl: 20, pill: 999 };

export const type = {
  h1:      { fontFamily: fonts.display, fontSize: 24, letterSpacing: 0.4, color: colors.bordeaux, textTransform: 'uppercase' },
  h2:      { fontFamily: fonts.display, fontSize: 17, letterSpacing: 0.6, color: colors.bordeaux, textTransform: 'uppercase' },
  score:   { fontFamily: fonts.display, fontSize: 48, letterSpacing: 2, color: '#FFFFFF' },
  kicker:  { fontFamily: fonts.bodyBold, fontSize: 10, letterSpacing: 1.6, textTransform: 'uppercase' },
  body:    { fontFamily: fonts.body, fontSize: 13, lineHeight: 20, color: colors.text },
  label:   { fontFamily: fonts.bodyBold, fontSize: 13, color: colors.ink },
  meta:    { fontFamily: fonts.body, fontSize: 11, color: colors.faint },
  stat:    { fontFamily: fonts.display, fontSize: 18, color: colors.bordeaux },
};

/**
 * Couleurs du rang au classement, d'après la zone renvoyée par l'API.
 *
 * Le nombre de places qualificatives est une donnée de la compétition, pas une
 * constante de l'application : il vaut 8 pour la 6e édition (quarts de finale
 * du Grand Prix) et pourrait changer d'une édition à l'autre. L'écran se
 * contente donc de colorer ce que le serveur a calculé.
 */
export const standingZone = (zone) => {
  if (zone === 'qualification') return { bg: colors.orange, fg: '#2A0A12' };
  if (zone === 'barrage') return { bg: '#C9BFB6', fg: '#FFFFFF' };
  return { bg: '#F0E6DA', fg: '#6B5A53' };
};
