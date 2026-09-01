import React from 'react';
import { ScrollView, View, Text } from 'react-native';
import { api, useQuery } from '../api';
import { colors, fonts, radius, spacing, type } from '../theme';
import { Card, Loader } from '../components/Ui';
import Icon from '../components/Icon';

export default function PlayerScreen({ route }) {
  const { id } = route.params;
  const q = useQuery(() => api.player(id), [id]);
  if (q.loading) return <Loader />;
  const p = q.data;
  if (!p) return null;

  const s = p.stats ?? {};
  const kpis = [
    [s.goals ?? 0, 'Buts'], [s.assists ?? 0, 'Passes déc.'], [s.appearances ?? 0, 'Matchs'],
    [s.minutes ?? 0, 'Minutes'], [s.yellow_cards ?? 0, 'Cartons jaunes'],
    [s.appearances > 0 ? (s.goals / s.appearances).toFixed(2).replace('.', ',') : '0', 'But / match'],
  ];
  const bars = p.goals_by_matchday ?? [];
  const max = Math.max(1, ...bars.map((b) => Number(b.goals)));

  return (
    <ScrollView>
      <View style={{ backgroundColor: colors.bordeauxMid, flexDirection: 'row', gap: spacing.lg, alignItems: 'flex-end', paddingHorizontal: 20, paddingBottom: 22, paddingTop: spacing.sm }}>
        <View style={{ width: 86, height: 86, borderRadius: 22, backgroundColor: 'rgba(253,244,232,0.14)', borderWidth: 1, borderColor: 'rgba(255,255,255,0.18)', alignItems: 'center', justifyContent: 'center' }}>
          <Icon name="user" size={40} color={colors.orangeSoft} strokeWidth={1.6} />
        </View>
        <View style={{ flex: 1 }}>
          <View style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.sm }}>
            <Text style={{ fontFamily: fonts.display, fontSize: 30, color: colors.orange }}>{p.jersey_number ?? '—'}</Text>
            <Text style={[type.kicker, { color: '#fff', backgroundColor: 'rgba(255,255,255,0.13)', paddingVertical: 5, paddingHorizontal: 9, borderRadius: 999, overflow: 'hidden' }]}>
              {p.position_label ?? p.position}
            </Text>
          </View>
          <Text style={{ fontFamily: fonts.display, fontSize: 24, color: '#fff', textTransform: 'uppercase', marginTop: 8, letterSpacing: 0.4 }}>
            {p.first_name}{'\n'}{p.last_name}
          </Text>
          <Text style={[type.meta, { color: colors.orangeSoft, marginTop: 8 }]}>
            {[p.team_name, p.age ? p.age + ' ans' : null, p.height_cm ? (p.height_cm / 100).toFixed(2).replace('.', ',') + ' m' : null].filter(Boolean).join(' · ')}
          </Text>
        </View>
      </View>

      <View style={{ padding: spacing.lg, gap: spacing.lg }}>
        <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: spacing.md }}>
          {kpis.map(([v, l]) => (
            <Card key={l} style={{ width: '31%', alignItems: 'center', paddingVertical: 12 }}>
              <Text style={[type.stat, { fontSize: 22 }]}>{v}</Text>
              <Text style={[type.kicker, { color: colors.faint, marginTop: 6, textAlign: 'center' }]}>{l}</Text>
            </Card>
          ))}
        </View>

        <Card style={{ gap: spacing.md }}>
          <Text style={type.h2}>Buts par journée</Text>
          <View style={{ flexDirection: 'row', alignItems: 'flex-end', gap: 7, height: 96 }}>
            {bars.map((b) => (
              <View key={b.matchday} style={{ flex: 1, alignItems: 'center', gap: 6, height: '100%', justifyContent: 'flex-end' }}>
                <View style={{ width: '100%', height: Math.max(2, (Number(b.goals) / max) * 74), backgroundColor: Number(b.goals) === max ? colors.brick : colors.orange, borderTopLeftRadius: 5, borderTopRightRadius: 5 }} />
                <Text style={[type.meta, { fontSize: 9 }]}>J{b.matchday}</Text>
              </View>
            ))}
          </View>
        </Card>

        <View style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.md, backgroundColor: colors.bordeaux, borderRadius: radius.lg, padding: spacing.lg }}>
          <Icon name="star" size={22} color={colors.orangeSoft} strokeWidth={1.8} />
          <Text style={{ flex: 1, fontFamily: fonts.bodyBold, fontSize: 12, lineHeight: 17, color: '#fff' }}>
            Fiche exportable en PDF pour les recruteurs · vidéos des buts disponibles
          </Text>
        </View>
      </View>
    </ScrollView>
  );
}
