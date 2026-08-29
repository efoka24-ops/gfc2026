import React from 'react';
import { ScrollView, View, Text, Image, Pressable, Linking } from 'react-native';
import { colors, fonts, radius, spacing, type } from '../theme';
import Icon from '../components/Icon';

const MISSIONS = [
  'Vulgariser les talents du Nord et leur donner de la visibilité.',
  'Mettre en avant le professionnalisme dans l\'organisation et sur le terrain.',
  'Permettre aux jeunes footballeurs d\'évoluer dans un milieu professionnel.',
];

const CONTACTS = [
  { icon: 'pin', text: 'Garoua, région du Nord · Cameroun' },
  { icon: 'mail', text: 'contact@garouafootballchallenge.cm', url: 'mailto:contact@garouafootballchallenge.cm' },
  { icon: 'phone', text: '+237 6 00 00 00 00', url: 'tel:+2376000000000' },
];

export default function AboutScreen({ navigation }) {
  return (
    <ScrollView>
      <View style={{ backgroundColor: colors.bordeauxMid, alignItems: 'center', paddingHorizontal: 20, paddingBottom: 26, paddingTop: spacing.md }}>
        <Image source={require('../../assets/logo.png')} style={{ width: 120, height: 120 }} resizeMode="contain" />
        <Text style={{ fontFamily: fonts.display, fontSize: 22, color: '#fff', textAlign: 'center', textTransform: 'uppercase', marginTop: 6, letterSpacing: 0.5 }}>
          Garoua Football{'\n'}Challenge
        </Text>
        <Text style={[type.kicker, { color: colors.orangeSoft, marginTop: 12 }]}>Since 2020 · 6e édition</Text>
      </View>

      <View style={{ padding: spacing.lg, gap: spacing.lg }}>
        <Text style={type.body}>
          Le Garoua Football Challenge est un championnat de vacances lancé chaque année pendant les congés scolaires.
          La 6e édition réunit 10 équipes réparties sur plusieurs compétitions : le championnat,
          le Grand Prix Gabriel MBAÏROBÉ et la Super Coupe.
        </Text>

        {MISSIONS.map((m, i) => (
          <View key={i} style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.md, backgroundColor: colors.card, borderWidth: 1, borderColor: colors.line, borderRadius: radius.md, padding: 13 }}>
            <View style={{ width: 34, height: 34, borderRadius: 10, backgroundColor: '#F6E9DB', alignItems: 'center', justifyContent: 'center' }}>
              <Text style={{ fontFamily: fonts.display, fontSize: 15, color: colors.brick }}>{i + 1}</Text>
            </View>
            <Text style={{ flex: 1, fontFamily: fonts.bodyBold, fontSize: 12.5, lineHeight: 18, color: colors.ink }}>{m}</Text>
          </View>
        ))}

        <Pressable
          onPress={() => navigation.navigate('Competitions')}
          style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.md, padding: spacing.lg, borderRadius: radius.lg, backgroundColor: colors.bordeaux }}
        >
          <Icon name="trophy" size={20} color={colors.orangeSoft} />
          <Text style={{ flex: 1, fontFamily: fonts.bodyBold, fontSize: 12.5, color: '#fff' }}>Voir les 3 compétitions de l'édition</Text>
          <Icon name="chevron" size={18} color={colors.orangeSoft} strokeWidth={2.2} />
        </Pressable>

        <View style={{ height: 1, backgroundColor: colors.line }} />

        {CONTACTS.map((c) => (
          <Pressable key={c.text} onPress={() => c.url && Linking.openURL(c.url)} style={{ flexDirection: 'row', alignItems: 'center', gap: 11 }}>
            <Icon name={c.icon} size={17} color={colors.brick} />
            <Text style={[type.label, { fontSize: 12.5 }]}>{c.text}</Text>
          </Pressable>
        ))}

        <View style={{ flexDirection: 'row', gap: 9 }}>
          <Pressable style={{ flex: 1, alignItems: 'center', paddingVertical: 12, borderRadius: radius.md, backgroundColor: colors.bordeaux }}>
            <Text style={{ fontFamily: fonts.bodyBold, fontSize: 11.5, color: '#fff' }}>Devenir partenaire</Text>
          </Pressable>
          <Pressable style={{ flex: 1, alignItems: 'center', paddingVertical: 12, borderRadius: radius.md, backgroundColor: colors.orange }}>
            <Text style={{ fontFamily: fonts.bodyBold, fontSize: 11.5, color: '#2A0A12' }}>Inscrire une équipe</Text>
          </Pressable>
        </View>

        {/* Accès à l'espace de saisie. Volontairement discret et en bas de
            page : il ne s'adresse qu'aux commissaires, commentateurs et
            organisateurs, pas au public (FR-036). */}
        <View style={{ height: 1, backgroundColor: colors.line, marginTop: spacing.md }} />
        <Pressable
          onPress={() => navigation.navigate('Connexion')}
          style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, paddingVertical: 14 }}
        >
          <Icon name="whistle" size={15} color={colors.faint} />
          <Text style={{ fontFamily: fonts.bodyBold, fontSize: 11.5, color: colors.faint }}>
            Espace opérateur
          </Text>
        </Pressable>
      </View>
    </ScrollView>
  );
}
