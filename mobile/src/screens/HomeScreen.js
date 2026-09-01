import React from 'react';
import { ScrollView, View, Text, Pressable, Image } from 'react-native';
import { api, useQuery } from '../api';
import { colors, fonts, radius, spacing, type } from '../theme';
import { Card, SectionTitle, LiveDot, Loader } from '../components/Ui';
import Crest from '../components/Crest';
import Icon from '../components/Icon';

export default function HomeScreen({ navigation }) {
  const fixtures = useQuery(() => api.fixtures(), []);
  const news = useQuery(() => api.news(6), []);
  const standings = useQuery(() => api.standings(), []);
  const scorers = useQuery(() => api.playerStats('goals'), []);

  if (fixtures.loading) return <Loader />;

  const all = fixtures.data ?? [];
  const featured = all.find((m) => m.status === 'live' || m.status === 'halftime') ?? all[0];
  const leader = (standings.data ?? [])[0];
  const topScorer = (scorers.data ?? [])[0];

  return (
    <ScrollView contentContainerStyle={{ padding: spacing.lg, gap: spacing.lg }}>
      {featured ? (
        <Pressable
          onPress={() => navigation.navigate('Match', { id: featured.id })}
          style={{ borderRadius: radius.xl, backgroundColor: colors.bordeauxMid, padding: spacing.lg, overflow: 'hidden' }}
        >
          <View style={{ flexDirection: 'row', alignItems: 'center' }}>
            {featured.status === 'live' || featured.status === 'halftime'
              ? <LiveDot minute={featured.minute} />
              : <Text style={[type.kicker, { color: '#fff' }]}>Prochain match</Text>}
            <Text style={[type.kicker, { color: colors.orangeSoft, marginLeft: 'auto' }]} numberOfLines={1}>
              {featured.competition}{featured.round_label ? ' · ' + featured.round_label : ''}
            </Text>
          </View>

          <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: spacing.lg }}>
            <View style={{ flex: 1, alignItems: 'center', gap: spacing.sm }}>
              <Crest team={{ abbr: featured.home_abbr, logo: featured.home_logo }} size={46} light />
              <Text numberOfLines={2} style={{ fontFamily: fonts.bodyBold, fontSize: 12, color: '#fff', textAlign: 'center' }}>{featured.home_name}</Text>
            </View>
            <Text style={type.score}>
              {featured.home_score == null ? new Date(featured.kickoff_at.replace(' ', 'T')).getHours() + 'h' : `${featured.home_score}–${featured.away_score}`}
            </Text>
            <View style={{ flex: 1, alignItems: 'center', gap: spacing.sm }}>
              <Crest team={{ abbr: featured.away_abbr, logo: featured.away_logo }} size={46} light />
              <Text numberOfLines={2} style={{ fontFamily: fonts.bodyBold, fontSize: 12, color: '#fff', textAlign: 'center' }}>{featured.away_name}</Text>
            </View>
          </View>

          <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.sm, marginTop: spacing.lg, paddingVertical: 12, borderRadius: radius.md, backgroundColor: colors.orange }}>
            <Text style={{ fontFamily: fonts.bodyBold, fontSize: 13, color: '#2A0A12' }}>Suivre le match</Text>
            <Icon name="chevron" size={16} color="#2A0A12" strokeWidth={2.4} />
          </View>
        </Pressable>
      ) : null}

      <View style={{ flexDirection: 'row', gap: spacing.md }}>
        <Card style={{ flex: 1, gap: 6 }} onPress={() => navigation.navigate('Classement')}>
          <Icon name="trophy" size={20} color={colors.brick} />
          <Text style={type.label}>Classement</Text>
          <Text style={type.meta}>{leader ? `${leader.name} · ${leader.points} pts` : '—'}</Text>
        </Card>
        <Card style={{ flex: 1, gap: 6 }} onPress={() => navigation.navigate('Stats')}>
          <Icon name="chart" size={20} color={colors.brick} />
          <Text style={type.label}>Meilleur buteur</Text>
          <Text style={type.meta}>{topScorer ? `${topScorer.name} · ${topScorer.value} buts` : '—'}</Text>
        </Card>
      </View>

      <SectionTitle action="Tout voir" onAction={() => navigation.navigate('Medias')}>Actualités</SectionTitle>

      {(news.data ?? []).map((n) => (
        <Card key={n.id} style={{ flexDirection: 'row', gap: spacing.md, alignItems: 'center' }}>
          {n.cover_image
            ? <Image source={{ uri: n.cover_image }} style={{ width: 74, height: 74, borderRadius: radius.md }} />
            : <View style={{ width: 74, height: 74, borderRadius: radius.md, backgroundColor: colors.bordeauxMid, alignItems: 'center', justifyContent: 'center' }}>
                <Icon name="image" size={22} color="rgba(255,255,255,0.75)" strokeWidth={1.8} />
              </View>}
          <View style={{ flex: 1, gap: 5 }}>
            <Text style={[type.kicker, { color: colors.brick }]}>{n.category}</Text>
            <Text style={{ fontFamily: fonts.bodyBold, fontSize: 13.5, lineHeight: 18, color: colors.ink }}>{n.title}</Text>
            <Text style={type.meta}>{new Date(n.published_at.replace(' ', 'T')).toLocaleDateString('fr-FR')}</Text>
          </View>
        </Card>
      ))}

      <Pressable
        onPress={() => navigation.navigate('Apropos')}
        style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.md, padding: spacing.lg, borderRadius: radius.lg, backgroundColor: colors.bordeaux }}
      >
        <Image source={require('../../assets/logo.png')} style={{ width: 42, height: 42 }} resizeMode="contain" />
        <Text style={{ flex: 1, fontFamily: fonts.bodyBold, fontSize: 12.5, lineHeight: 18, color: '#fff' }}>
          Le GFC, c'est vulgariser les talents et faire évoluer les jeunes dans un milieu professionnel.
        </Text>
        <Icon name="chevron" size={18} color={colors.orangeSoft} strokeWidth={2.2} />
      </Pressable>
    </ScrollView>
  );
}
