import React from 'react';
import { ScrollView, View, Text, Pressable } from 'react-native';
import { api, useQuery } from '../api';
import { colors, fonts, radius, spacing, type } from '../theme';
import { Loader } from '../components/Ui';
import Crest from '../components/Crest';
import Icon from '../components/Icon';

const GROUPS = [
  ['GB', 'Gardiens'], ['DEF', 'Défenseurs'], ['MIL', 'Milieux'], ['ATT', 'Attaquants'],
];

export default function SquadScreen({ route, navigation }) {
  const { id } = route.params;
  const q = useQuery(() => api.team(id), [id]);
  if (q.loading) return <Loader />;
  const team = q.data;
  if (!team) return null;
  const s = team.standing ?? {};

  return (
    <ScrollView>
      <View style={{ backgroundColor: colors.bordeauxMid, flexDirection: 'row', alignItems: 'center', gap: 14, paddingHorizontal: 18, paddingBottom: 18, paddingTop: 4 }}>
        <Crest team={team} size={58} light />
        <View style={{ flex: 1 }}>
          <Text style={{ fontFamily: fonts.display, fontSize: 22, color: '#fff', textTransform: 'uppercase', letterSpacing: 0.4 }}>{team.name}</Text>
          <Text style={[type.meta, { color: colors.orangeSoft, marginTop: 6 }]}>
            {[team.quarter, team.founded_year ? 'fondé en ' + team.founded_year : null, team.squad.length + ' joueurs'].filter(Boolean).join(' · ')}
          </Text>
        </View>
      </View>

      <View style={{ flexDirection: 'row', backgroundColor: colors.bordeaux }}>
        {[[s.points, 'Points'], [s.played, 'Matchs'], [s.goals_for, 'Buts pour'], [s.goals_against, 'Buts contre']].map(([v, l]) => (
          <View key={l} style={{ flex: 1, paddingVertical: 12, alignItems: 'center', borderLeftWidth: 1, borderLeftColor: 'rgba(255,255,255,0.09)' }}>
            <Text style={{ fontFamily: fonts.display, fontSize: 19, color: colors.orangeSoft }}>{v ?? '—'}</Text>
            <Text style={[type.kicker, { color: 'rgba(255,255,255,0.8)', marginTop: 5 }]}>{l}</Text>
          </View>
        ))}
      </View>

      <View style={{ padding: spacing.lg, gap: spacing.md }}>
        {GROUPS.map(([pos, label]) => {
          const players = team.squad.filter((p) => p.position === pos);
          if (!players.length) return null;
          return (
            <View key={pos} style={{ gap: spacing.sm }}>
              <Text style={[type.kicker, { color: colors.faint, marginTop: spacing.sm }]}>{label}</Text>
              {players.map((p) => (
                <Pressable
                  key={p.id}
                  onPress={() => navigation.navigate('Joueur', { id: p.id })}
                  style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.md, backgroundColor: colors.card, borderWidth: 1, borderColor: colors.line, borderRadius: radius.md, padding: 10 }}
                >
                  <Text style={{ width: 30, textAlign: 'center', fontFamily: fonts.display, fontSize: 17, color: colors.orange }}>{p.jersey_number ?? '—'}</Text>
                  <View style={{ width: 38, height: 38, borderRadius: 19, backgroundColor: '#EFE4D6', alignItems: 'center', justifyContent: 'center' }}>
                    <Icon name="user" size={18} color="#B3A08F" strokeWidth={1.9} />
                  </View>
                  <View style={{ flex: 1 }}>
                    <Text style={type.label}>{p.first_name} {p.last_name}</Text>
                    <Text style={[type.meta, { marginTop: 3 }]}>
                      {[p.position_label, p.age ? p.age + ' ans' : null].filter(Boolean).join(' · ')}
                    </Text>
                  </View>
                  <Icon name="chevron" size={16} color={colors.faint} />
                </Pressable>
              ))}
            </View>
          );
        })}
      </View>
    </ScrollView>
  );
}
