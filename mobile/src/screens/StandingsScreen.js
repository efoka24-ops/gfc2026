import React, { useState } from 'react';
import { ScrollView, View, Text, Pressable } from 'react-native';
import { api, useQuery } from '../api';
import { colors, fonts, radius, spacing, standingZone, type } from '../theme';
import { Chip, Loader } from '../components/Ui';

const COLS = [
  { key: 'played', label: 'J' }, { key: 'won', label: 'G' },
  { key: 'drawn', label: 'N' }, { key: 'lost', label: 'P' },
];

export default function StandingsScreen({ navigation }) {
  const [competition, setCompetition] = useState('championnat');
  const q = useQuery(() => api.standings(competition), [competition]);
  const rows = q.data ?? [];

  return (
    <ScrollView contentContainerStyle={{ padding: spacing.md, gap: spacing.md }}>
      <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ paddingHorizontal: 4 }}>
        <Chip label="Championnat" active={competition === 'championnat'} onPress={() => setCompetition('championnat')} />
        <Chip label="Poule Grand Prix" active={competition === 'grand-prix-mbairobe'} onPress={() => setCompetition('grand-prix-mbairobe')} />
      </ScrollView>

      {q.loading ? <Loader /> : null}

      <View style={{ backgroundColor: colors.card, borderWidth: 1, borderColor: colors.line, borderRadius: radius.lg, overflow: 'hidden' }}>
        <View style={{ flexDirection: 'row', paddingVertical: 10, paddingHorizontal: 12, backgroundColor: colors.bordeaux }}>
          <Text style={[head, { width: 26 }]} />
          <Text style={[head, { flex: 1, textAlign: 'left', paddingLeft: 8 }]}>Équipe</Text>
          {COLS.map((c) => <Text key={c.key} style={[head, { width: 22 }]}>{c.label}</Text>)}
          <Text style={[head, { width: 34 }]}>Diff</Text>
          <Text style={[head, { width: 30 }]}>Pts</Text>
        </View>

        {rows.map((r, i) => {
          // Le rang et la zone viennent du serveur : le classement affiche ne
          // peut pas diverger de celui que calcule la vue v_standings.
          const pos = r.rank ?? i + 1;
          const zone = standingZone(r.zone);
          const diff = Number(r.goal_diff);
          return (
            <Pressable
              key={r.team_id}
              onPress={() => navigation.navigate('Effectif', { id: r.team_id, name: r.name })}
              style={{ flexDirection: 'row', alignItems: 'center', paddingVertical: 11, paddingHorizontal: 12, borderBottomWidth: 1, borderBottomColor: 'rgba(90,20,36,0.06)', backgroundColor: pos % 2 ? colors.card : '#FDF9F3' }}
            >
              <View style={{ width: 26 }}>
                <View style={{ width: 19, height: 19, borderRadius: 6, backgroundColor: zone.bg, alignItems: 'center', justifyContent: 'center' }}>
                  <Text style={{ fontFamily: fonts.bodyBold, fontSize: 10, color: zone.fg }}>{pos}</Text>
                </View>
              </View>
              <Text numberOfLines={1} style={[type.label, { flex: 1, paddingLeft: 8, fontSize: 12 }]}>{r.name}</Text>
              {COLS.map((c) => <Text key={c.key} style={cell}>{r[c.key]}</Text>)}
              <Text style={[cell, { width: 34, fontFamily: fonts.bodyBold, color: diff > 0 ? colors.green : diff < 0 ? colors.red : colors.muted }]}>
                {diff > 0 ? '+' + diff : diff}
              </Text>
              <Text style={[cell, { width: 30, fontFamily: fonts.display, fontSize: 15, color: colors.bordeaux }]}>{r.points}</Text>
            </Pressable>
          );
        })}
      </View>

      <View style={{ flexDirection: 'row', gap: spacing.lg, paddingHorizontal: 6 }}>
        <Legend color={colors.orange} label="Qualifié demi-finales" />
        <Legend color="#C9BFB6" label="Barrages" />
      </View>
    </ScrollView>
  );
}

const head = { fontFamily: fonts.bodyBold, fontSize: 9.5, letterSpacing: 0.6, color: '#fff', textAlign: 'center' };
const cell = { width: 22, textAlign: 'center', fontFamily: fonts.body, fontSize: 11.5, color: '#4A3A34' };

function Legend({ color, label }) {
  return (
    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
      <View style={{ width: 9, height: 9, borderRadius: 3, backgroundColor: color }} />
      <Text style={type.meta}>{label}</Text>
    </View>
  );
}
