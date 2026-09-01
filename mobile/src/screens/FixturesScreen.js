import React, { useState } from 'react';
import { ScrollView, View, Text, Pressable } from 'react-native';
import { api, useQuery } from '../api';
import { colors, fonts, radius, spacing, type } from '../theme';
import { Chip, Segmented, Loader, EmptyState } from '../components/Ui';
import Icon from '../components/Icon';

const FILTERS = [
  { label: 'Toutes', value: null },
  { label: 'Championnat', value: 'championnat' },
  { label: 'Grand Prix', value: 'grand-prix-mbairobe' },
  { label: 'Super Coupe', value: 'super-coupe' },
];

const dayLabel = (iso) =>
  new Date(iso.replace(' ', 'T')).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });

export default function FixturesScreen({ navigation }) {
  const [scope, setScope] = useState('upcoming');
  const [competition, setCompetition] = useState(null);
  const q = useQuery(
    () => (scope === 'upcoming' ? api.fixtures(competition) : api.results(competition)),
    [scope, competition]
  );

  const matches = q.data ?? [];
  let lastDay = null;

  return (
    <ScrollView contentContainerStyle={{ padding: spacing.lg, gap: spacing.md }}>
      <ScrollView horizontal showsHorizontalScrollIndicator={false}>
        {FILTERS.map((f) => (
          <Chip key={f.label} label={f.label} active={competition === f.value} onPress={() => setCompetition(f.value)} />
        ))}
      </ScrollView>

      <Segmented
        value={scope}
        onChange={setScope}
        options={[{ label: 'À venir', value: 'upcoming' }, { label: 'Résultats', value: 'results' }]}
      />

      {q.loading ? <Loader /> : null}
      {!q.loading && matches.length === 0
        ? <EmptyState title="Aucun match" subtitle="Le calendrier de cette compétition n'est pas encore publié." />
        : null}

      {matches.map((m) => {
        const day = dayLabel(m.kickoff_at);
        const showHead = day !== lastDay;
        lastDay = day;
        const finished = m.status === 'finished';
        const kickoff = new Date(m.kickoff_at.replace(' ', 'T'));

        return (
          <View key={m.id} style={{ gap: spacing.sm }}>
            {showHead ? <Text style={[type.kicker, { color: colors.faint, marginTop: spacing.sm }]}>{day}</Text> : null}
            <Pressable
              onPress={() => navigation.navigate('Match', { id: m.id })}
              style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.md, backgroundColor: colors.card, borderWidth: 1, borderColor: colors.line, borderRadius: radius.lg, padding: 13 }}
            >
              <View style={{ width: 46, alignItems: 'center' }}>
                <Text style={[type.stat, { fontSize: 16 }]}>
                  {finished ? `${m.home_score}–${m.away_score}` : `${kickoff.getHours()}h${String(kickoff.getMinutes()).padStart(2, '0')}`}
                </Text>
                <Text style={[type.meta, { fontSize: 9.5, marginTop: 4, textAlign: 'center' }]} numberOfLines={2}>
                  {m.matchday ? 'J' + m.matchday : (m.round_label ?? m.competition)}
                </Text>
              </View>
              <View style={{ width: 1, alignSelf: 'stretch', backgroundColor: colors.line }} />
              <View style={{ flex: 1, gap: 7 }}>
                <Row abbr={m.home_abbr} name={m.home_name} />
                <Row abbr={m.away_abbr} name={m.away_name} />
                <View style={{ flexDirection: 'row', alignItems: 'center', gap: 5 }}>
                  <Icon name="pin" size={11} color={colors.faint} />
                  <Text style={type.meta}>{m.venue ?? 'Stade à confirmer'}</Text>
                </View>
              </View>
              {m.status === 'live' || m.status === 'halftime'
                ? <View style={{ width: 8, height: 8, borderRadius: 4, backgroundColor: colors.live }} />
                : null}
            </Pressable>
          </View>
        );
      })}
    </ScrollView>
  );
}

function Row({ abbr, name }) {
  return (
    <View style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.sm }}>
      <Text style={{ width: 32, fontFamily: fonts.display, fontSize: 11, color: colors.brick }}>{abbr}</Text>
      <Text numberOfLines={1} style={[type.label, { flex: 1 }]}>{name}</Text>
    </View>
  );
}
