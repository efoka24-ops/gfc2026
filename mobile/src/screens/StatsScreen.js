import React, { useState } from 'react';
import { ScrollView, View, Text, Pressable } from 'react-native';
import { api, useQuery } from '../api';
import { colors, fonts, radius, spacing, type } from '../theme';
import { Card, Segmented, Loader, MetricRow } from '../components/Ui';

export default function StatsScreen({ navigation }) {
  const [tab, setTab] = useState('joueurs');
  const goals = useQuery(() => api.playerStats('goals'), []);
  const assists = useQuery(() => api.playerStats('assists'), []);
  const cards = useQuery(() => api.playerStats('cards'), []);
  const teams = useQuery(() => api.teamStats(), []);

  const topScorer = (goals.data ?? [])[0];
  const topAssist = (assists.data ?? [])[0];

  return (
    <ScrollView contentContainerStyle={{ padding: spacing.lg, gap: spacing.lg }}>
      <Segmented
        value={tab}
        onChange={setTab}
        options={[{ label: 'Joueurs', value: 'joueurs' }, { label: 'Équipes', value: 'equipes' }]}
      />

      {tab === 'joueurs' ? (
        <>
          <View style={{ flexDirection: 'row', gap: spacing.md }}>
            <Highlight kicker="Soulier d'or" value={topScorer?.value} name={topScorer?.name} sub={topScorer ? `${topScorer.team} · ${topScorer.appearances} matchs` : '—'} bg={colors.brick} kickerColor="#FFE0BE" />
            <Highlight kicker="Passeur" value={topAssist?.value} name={topAssist?.name} sub={topAssist ? `${topAssist.team} · ${topAssist.appearances} matchs` : '—'} bg={colors.bordeaux} kickerColor={colors.orangeSoft} />
          </View>

          {goals.loading ? <Loader /> : null}

          <RankBlock title="Meilleurs buteurs" color={colors.orange} rows={goals.data} navigation={navigation} />
          <RankBlock title="Meilleures passes décisives" color={colors.bordeaux} rows={assists.data} navigation={navigation} />
          <RankBlock title="Discipline · cartons" color={colors.yellow} rows={cards.data} navigation={navigation} />
        </>
      ) : (
        <>
          {teams.loading ? <Loader /> : null}
          <TeamBlock title="Attaque · buts marqués" unit="buts" rows={teams.data?.attack} valueKey="goals_for" color={colors.bordeauxMid} />
          <TeamBlock title="Défense · buts encaissés" unit="buts" rows={teams.data?.defence} valueKey="goals_against" color={colors.green} />
          <TeamBlock title="Possession moyenne" unit="%" rows={teams.data?.possession} valueKey="value" color={colors.bordeaux} />
          <TeamBlock title="Affluence par match" unit="spectateurs" rows={teams.data?.attendance} valueKey="value" color={colors.orange} />
        </>
      )}
    </ScrollView>
  );
}

function Highlight({ kicker, value, name, sub, bg, kickerColor }) {
  return (
    <View style={{ flex: 1, backgroundColor: bg, borderRadius: radius.xl, padding: spacing.lg }}>
      <Text style={[type.kicker, { color: kickerColor }]}>{kicker}</Text>
      <Text style={{ fontFamily: fonts.display, fontSize: 30, color: '#fff', marginTop: spacing.md }}>{value ?? '—'}</Text>
      <Text numberOfLines={2} style={{ fontFamily: fonts.bodyBold, fontSize: 12.5, color: '#fff', marginTop: spacing.sm }}>{name ?? '—'}</Text>
      <Text style={[type.meta, { color: 'rgba(255,255,255,0.8)', marginTop: 4 }]} numberOfLines={1}>{sub}</Text>
    </View>
  );
}

function RankBlock({ title, color, rows, navigation }) {
  if (!rows?.length) return null;
  return (
    <View style={{ backgroundColor: colors.card, borderWidth: 1, borderColor: colors.line, borderRadius: radius.lg, overflow: 'hidden' }}>
      <View style={{ flexDirection: 'row', alignItems: 'center', gap: 9, padding: 13, borderBottomWidth: 1, borderBottomColor: 'rgba(90,20,36,0.07)' }}>
        <View style={{ width: 7, height: 7, borderRadius: 2, backgroundColor: color }} />
        <Text style={type.h2}>{title}</Text>
      </View>
      {rows.slice(0, 5).map((r, i) => (
        <Pressable
          key={r.id ?? i}
          onPress={() => r.id && navigation.navigate('Joueur', { id: r.id })}
          style={{ flexDirection: 'row', alignItems: 'center', gap: 11, paddingVertical: 11, paddingHorizontal: 15, borderBottomWidth: 1, borderBottomColor: 'rgba(90,20,36,0.05)' }}
        >
          <Text style={{ width: 18, fontFamily: fonts.display, fontSize: 14, color: colors.faint }}>{i + 1}</Text>
          <View style={{ flex: 1 }}>
            <Text style={[type.label, { fontSize: 12.5 }]}>{r.name}</Text>
            <Text style={[type.meta, { marginTop: 3 }]}>{r.team}</Text>
          </View>
          <Text style={type.stat}>{r.value}</Text>
        </Pressable>
      ))}
    </View>
  );
}

function TeamBlock({ title, unit, rows, valueKey, color }) {
  if (!rows?.length) return null;
  const max = Math.max(...rows.map((r) => Number(r[valueKey]) || 0), 1);
  return (
    <Card style={{ gap: spacing.md }}>
      <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'baseline' }}>
        <Text style={type.h2}>{title}</Text>
        <Text style={type.meta}>{unit}</Text>
      </View>
      {rows.map((r) => (
        <MetricRow key={r.name} name={r.name} value={r[valueKey]} max={max} color={color} />
      ))}
    </Card>
  );
}
