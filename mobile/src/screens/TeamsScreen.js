import React, { useMemo, useState } from 'react';
import { ScrollView, View, Text, TextInput, Pressable } from 'react-native';
import { api, useQuery } from '../api';
import { colors, radius, spacing, type } from '../theme';
import { Loader } from '../components/Ui';
import Crest from '../components/Crest';
import Icon from '../components/Icon';

export default function TeamsScreen({ navigation }) {
  const [search, setSearch] = useState('');
  const teams = useQuery(() => api.teams(), []);
  const standings = useQuery(() => api.standings(), []);

  const points = useMemo(() => {
    const map = {};
    (standings.data ?? []).forEach((r, i) => { map[r.team_id] = { points: r.points, pos: i + 1 }; });
    return map;
  }, [standings.data]);

  const list = (teams.data ?? []).filter((t) => t.name.toLowerCase().includes(search.toLowerCase()));

  return (
    <ScrollView contentContainerStyle={{ padding: spacing.lg, gap: spacing.md }}>
      <View style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.md, backgroundColor: colors.card, borderWidth: 1, borderColor: colors.line, borderRadius: radius.md, paddingHorizontal: 14 }}>
        <Icon name="search" size={16} color={colors.faint} strokeWidth={2.2} />
        <TextInput
          value={search}
          onChangeText={setSearch}
          placeholder="Rechercher une équipe, un joueur…"
          placeholderTextColor={colors.faint}
          style={{ flex: 1, paddingVertical: 12, fontSize: 13, color: colors.ink }}
        />
      </View>

      {teams.loading ? <Loader /> : null}

      {list.map((t) => (
        <Pressable
          key={t.id}
          onPress={() => navigation.navigate('Effectif', { id: t.id, name: t.name })}
          style={{ flexDirection: 'row', alignItems: 'center', gap: 13, backgroundColor: colors.card, borderWidth: 1, borderColor: colors.line, borderRadius: radius.lg, padding: 13 }}
        >
          <Crest team={t} size={44} />
          <View style={{ flex: 1 }}>
            <Text style={type.label}>{t.name}</Text>
            <Text style={[type.meta, { marginTop: 4 }]}>
              {[t.quarter, points[t.id] ? points[t.id].pos + 'e du championnat' : null].filter(Boolean).join(' · ')}
            </Text>
          </View>
          <View style={{ alignItems: 'flex-end' }}>
            <Text style={type.stat}>{points[t.id]?.points ?? '—'}</Text>
            <Text style={[type.kicker, { color: colors.faint, marginTop: 4 }]}>pts</Text>
          </View>
        </Pressable>
      ))}
    </ScrollView>
  );
}
