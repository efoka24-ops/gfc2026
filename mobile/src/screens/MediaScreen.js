import React, { useState } from 'react';
import { ScrollView, View, Text, Image, Pressable, Linking, useWindowDimensions } from 'react-native';
import { api, useQuery } from '../api';
import { colors, fonts, radius, spacing, type } from '../theme';
import { Chip, Loader, EmptyState } from '../components/Ui';
import Icon from '../components/Icon';

export default function MediaScreen() {
  const [type_, setType] = useState(null);
  const q = useQuery(() => api.media(type_), [type_]);
  const { width } = useWindowDimensions();
  const tile = (width - spacing.lg * 2 - spacing.md) / 2;

  const items = q.data ?? [];
  const [featured, ...rest] = items;

  return (
    <ScrollView contentContainerStyle={{ padding: spacing.lg, gap: spacing.lg }}>
      <ScrollView horizontal showsHorizontalScrollIndicator={false}>
        <Chip label="Tout" active={type_ === null} onPress={() => setType(null)} />
        <Chip label="Photos" active={type_ === 'photo'} onPress={() => setType('photo')} />
        <Chip label="Vidéos" active={type_ === 'video'} onPress={() => setType('video')} />
      </ScrollView>

      {q.loading ? <Loader /> : null}
      {!q.loading && !items.length
        ? <EmptyState icon="image" title="Aucun média" subtitle="Les photos et vidéos de l'édition seront publiées ici." />
        : null}

      {featured ? (
        <Pressable onPress={() => Linking.openURL(featured.url)} style={{ height: 184, borderRadius: radius.xl, backgroundColor: colors.bordeauxMid, overflow: 'hidden', justifyContent: 'flex-end', padding: spacing.lg }}>
          {featured.thumbnail ? <Image source={{ uri: featured.thumbnail }} style={{ position: 'absolute', width: '100%', height: '100%', opacity: 0.55 }} /> : null}
          <View style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0, alignItems: 'center', justifyContent: 'center' }}>
            <View style={{ width: 54, height: 54, borderRadius: 27, backgroundColor: colors.orange, alignItems: 'center', justifyContent: 'center' }}>
              <Icon name="play" size={20} color="#2A0A12" filled />
            </View>
          </View>
          <Text style={[type.kicker, { color: colors.orangeSoft }]}>
            {featured.type === 'video' ? 'Vidéo' : 'Photo'}
            {featured.duration_seconds ? ` · ${Math.floor(featured.duration_seconds / 60)}:${String(featured.duration_seconds % 60).padStart(2, '0')}` : ''}
          </Text>
          <Text style={{ fontFamily: fonts.display, fontSize: 18, color: '#fff', textTransform: 'uppercase', marginTop: 7, letterSpacing: 0.4 }}>{featured.title}</Text>
        </Pressable>
      ) : null}

      <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: spacing.md }}>
        {rest.map((m) => (
          <Pressable key={m.id} onPress={() => Linking.openURL(m.url)} style={{ width: tile, height: 118, borderRadius: radius.md, backgroundColor: colors.bordeaux, overflow: 'hidden', justifyContent: 'flex-end', padding: 10 }}>
            {m.thumbnail ? <Image source={{ uri: m.thumbnail }} style={{ position: 'absolute', width: '100%', height: '100%', opacity: 0.6 }} /> : null}
            <View style={{ position: 'absolute', top: 9, right: 9, paddingVertical: 4, paddingHorizontal: 7, borderRadius: 6, backgroundColor: 'rgba(20,6,10,0.55)' }}>
              <Text style={[type.kicker, { color: '#fff', fontSize: 8.5 }]}>{m.type}</Text>
            </View>
            <Text numberOfLines={2} style={{ fontFamily: fonts.bodyBold, fontSize: 11, lineHeight: 15, color: '#fff' }}>{m.title}</Text>
          </Pressable>
        ))}
      </View>
    </ScrollView>
  );
}
