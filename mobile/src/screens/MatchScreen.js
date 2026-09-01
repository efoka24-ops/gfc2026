import React, { useState } from 'react';
import { ScrollView, View, Text } from 'react-native';
import { api, usePolling } from '../api';
import { colors, fonts, radius, spacing, type } from '../theme';
import { Card, Segmented, LiveDot, Loader, StatBar } from '../components/Ui';
import Crest from '../components/Crest';

const EVENT_LABEL = {
  goal: 'But', penalty: 'Penalty', own_goal: 'But contre son camp',
  yellow: 'Carton jaune', red: 'Carton rouge', sub: 'Changement',
  kickoff: "Coup d'envoi", halftime: 'Mi-temps', fulltime: 'Fin du match',
  penalty_missed: 'Penalty manqué', var: 'Décision arbitrale',
};

const eventMark = (type) => {
  if (type === 'yellow') return { bg: '#FFF4D6', dot: colors.yellow, w: 8, h: 11, r: 2 };
  if (type === 'red') return { bg: '#FDECEC', dot: colors.red, w: 8, h: 11, r: 2 };
  if (type === 'sub') return { bg: '#E6EFE6', dot: colors.green, w: 11, h: 11, r: 6 };
  if (['goal', 'penalty', 'own_goal'].includes(type)) return { bg: '#F6E9DB', dot: colors.bordeauxMid, w: 12, h: 12, r: 6 };
  return { bg: '#EFE4D6', dot: colors.faint, w: 10, h: 10, r: 5 };
};

export default function MatchScreen({ route }) {
  const { id } = route.params;
  const [tab, setTab] = useState('resume');
  // rafraîchissement toutes les 15 s tant que l'écran est ouvert
  const { data: m, loading } = usePolling(() => api.match(id), [id], 15000);

  if (loading && !m) return <Loader />;
  if (!m) return null;

  const live = m.status === 'live' || m.status === 'halftime';
  const homeStats = m.stats?.find((s) => s.team_id === m.home_id) ?? {};
  const awayStats = m.stats?.find((s) => s.team_id === m.away_id) ?? {};

  return (
    <ScrollView>
      <View style={{ backgroundColor: colors.bordeauxMid, paddingHorizontal: 18, paddingBottom: spacing.xl, paddingTop: spacing.sm }}>
        <View style={{ alignItems: 'center' }}>
          {live
            ? <LiveDot minute={m.minute} label={m.status === 'halftime' ? 'Mi-temps' : 'En direct'} />
            : <Text style={[type.kicker, { color: '#fff' }]}>{m.status === 'finished' ? 'Terminé' : 'Programmé'}</Text>}
        </View>
        <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: spacing.lg }}>
          <View style={{ flex: 1, alignItems: 'center', gap: 9 }}>
            <Crest team={{ abbr: m.home_abbr, logo: m.home_logo }} size={56} light />
            <Text numberOfLines={2} style={{ fontFamily: fonts.bodyBold, fontSize: 12.5, color: '#fff', textAlign: 'center' }}>{m.home_name}</Text>
          </View>
          <View style={{ alignItems: 'center' }}>
            <Text style={type.score}>{m.home_score == null ? '—' : `${m.home_score}–${m.away_score}`}</Text>
            <Text style={[type.kicker, { color: colors.orangeSoft, marginTop: 6 }]} numberOfLines={1}>{m.venue}</Text>
          </View>
          <View style={{ flex: 1, alignItems: 'center', gap: 9 }}>
            <Crest team={{ abbr: m.away_abbr, logo: m.away_logo }} size={56} light />
            <Text numberOfLines={2} style={{ fontFamily: fonts.bodyBold, fontSize: 12.5, color: '#fff', textAlign: 'center' }}>{m.away_name}</Text>
          </View>
        </View>
      </View>

      <View style={{ padding: spacing.lg, gap: spacing.lg }}>
        <Segmented
          value={tab}
          onChange={setTab}
          options={[{ label: 'Résumé', value: 'resume' }, { label: 'Compos', value: 'compos' }, { label: 'Stats', value: 'stats' }]}
        />

        {tab === 'resume' ? (
          <View>
            {(m.events ?? []).map((e) => {
              const mark = eventMark(e.type);
              return (
                <View key={e.id} style={{ flexDirection: 'row', gap: spacing.md, paddingBottom: spacing.lg }}>
                  <Text style={[type.stat, { width: 36, textAlign: 'right', fontSize: 14 }]}>{e.minute}'</Text>
                  <View style={{ width: 26, alignItems: 'center' }}>
                    <View style={{ width: 26, height: 26, borderRadius: 13, backgroundColor: mark.bg, alignItems: 'center', justifyContent: 'center' }}>
                      <View style={{ width: mark.w, height: mark.h, borderRadius: mark.r, backgroundColor: mark.dot }} />
                    </View>
                    <View style={{ flex: 1, width: 2, backgroundColor: colors.line, marginTop: 4 }} />
                  </View>
                  <View style={{ flex: 1 }}>
                    <Text style={type.label}>
                      {EVENT_LABEL[e.type] ?? e.type}{e.player ? ' · ' + e.player : ''}
                    </Text>
                    {e.related_player || e.detail ? (
                      <Text style={[type.meta, { marginTop: 3 }]}>
                        {[e.related_player ? 'Passe décisive : ' + e.related_player : null, e.detail].filter(Boolean).join(' · ')}
                      </Text>
                    ) : null}
                  </View>
                </View>
              );
            })}
          </View>
        ) : null}

        {tab === 'compos' ? (
          <View style={{ gap: spacing.md }}>
            {[[m.home_id, m.home_name], [m.away_id, m.away_name]].map(([tid, tname]) => (
              <Card key={tid} style={{ gap: spacing.md }}>
                <Text style={type.h2}>{tname}</Text>
                {(m.lineups ?? []).filter((l) => l.team_id === tid).map((l, i) => (
                  <View key={i} style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.md }}>
                    <Text style={[type.stat, { width: 26, fontSize: 15, color: colors.orange }]}>{l.jersey_number}</Text>
                    <Text style={[type.label, { flex: 1 }]}>{l.name}</Text>
                    <Text style={type.meta}>{l.is_starter ? l.position_label : 'Remplaçant'}</Text>
                  </View>
                ))}
              </Card>
            ))}
          </View>
        ) : null}

        {tab === 'stats' ? (
          <Card style={{ gap: spacing.md }}>
            <Text style={type.h2}>Statistiques du match</Text>
            <StatBar label="Possession" left={(homeStats.possession ?? 0) + ' %'} right={(awayStats.possession ?? 0) + ' %'} leftValue={homeStats.possession} rightValue={awayStats.possession} />
            <StatBar label="Tirs" left={homeStats.shots ?? 0} right={awayStats.shots ?? 0} leftValue={homeStats.shots} rightValue={awayStats.shots} />
            <StatBar label="Tirs cadrés" left={homeStats.shots_on_target ?? 0} right={awayStats.shots_on_target ?? 0} leftValue={homeStats.shots_on_target} rightValue={awayStats.shots_on_target} />
            <StatBar label="Corners" left={homeStats.corners ?? 0} right={awayStats.corners ?? 0} leftValue={homeStats.corners} rightValue={awayStats.corners} />
            <StatBar label="Fautes" left={homeStats.fouls ?? 0} right={awayStats.fouls ?? 0} leftValue={homeStats.fouls} rightValue={awayStats.fouls} />
          </Card>
        ) : null}
      </View>
    </ScrollView>
  );
}
