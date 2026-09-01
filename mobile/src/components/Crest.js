import React from 'react';
import { View, Text, Image } from 'react-native';
import { colors, fonts, radius } from '../theme';

/** Écusson d'équipe : logo si disponible, sinon abréviation sur fond couleur club. */
export default function Crest({ team, size = 44, light = false }) {
  const label = team?.abbr ?? team?.home_abbr ?? '—';
  if (team?.logo) {
    return <Image source={{ uri: team.logo }} style={{ width: size, height: size, borderRadius: radius.md }} resizeMode="contain" />;
  }
  return (
    <View style={{
      width: size, height: size, borderRadius: size * 0.3,
      backgroundColor: light ? colors.cream : (team?.color ?? colors.bordeauxMid),
      alignItems: 'center', justifyContent: 'center',
    }}>
      <Text style={{ fontFamily: fonts.display, fontSize: size * 0.32, color: light ? colors.bordeauxMid : '#fff' }}>
        {label}
      </Text>
    </View>
  );
}
