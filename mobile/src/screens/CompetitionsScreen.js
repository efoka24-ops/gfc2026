import React from 'react';
import { ScrollView, View, Text } from 'react-native';
import { api, useQuery } from '../api';
import { colors, fonts, radius, spacing, type } from '../theme';
import { Loader } from '../components/Ui';

const TYPE_LABEL = {
  league: 'Championnat de vacances',
  cup: 'Coupe · élimination directe',
  supercup: 'Match unique',
};

const HEADER_BG = { league: colors.brick, cup: colors.bordeauxMid, supercup: colors.bordeauxDeep };

const fmt = (d) => (d ? new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' }) : '—');

export default function CompetitionsScreen() {
  const q = useQuery(() => api.competitions(), []);
  if (q.loading) return <Loader />;

  return (
    <ScrollView contentContainerStyle={{ padding: spacing.lg, gap: spacing.md }}>
      {(q.data ?? []).map((c) => (
        <View key={c.id} style={{ borderRadius: radius.xl, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.card, overflow: 'hidden' }}>
          <View style={{ backgroundColor: HEADER_BG[c.type] ?? colors.bordeaux, paddingHorizontal: 16, paddingVertical: 15 }}>
            <Text style={[type.kicker, { color: colors.orangeSoft }]}>{TYPE_LABEL[c.type] ?? c.type}</Text>
            <Text style={{ fontFamily: fonts.display, fontSize: 20, color: '#fff', textTransform: 'uppercase', marginTop: 7, letterSpacing: 0.4 }}>{c.name}</Text>
          </View>
          <View style={{ padding: 16, gap: spacing.md }}>
            <Text style={type.body}>{c.description}</Text>
            <View style={{ flexDirection: 'row', borderTopWidth: 1, borderTopColor: 'rgba(90,20,36,0.08)', paddingTop: spacing.md }}>
              <Fact value={c.team_count} label="Équipes" />
              <Fact value={c.match_count} label="Matchs" />
              <Fact value={fmt(c.end_date)} label="Fin" />
            </View>
          </View>
        </View>
      ))}
    </ScrollView>
  );
}

function Fact({ value, label }) {
  return (
    <View style={{ flex: 1 }}>
      <Text style={[type.stat, { fontSize: 17 }]}>{value ?? '—'}</Text>
      <Text style={[type.kicker, { color: colors.faint, marginTop: 5 }]}>{label}</Text>
    </View>
  );
}
