import React, { useEffect } from 'react';
import { View, Text, ScrollView, Pressable } from 'react-native';

import { api, usePolling } from '../../api';
import { useOperateur } from '../../auth';
import { useFileEnAttente, viderFile } from '../../queue';
import { Card, Loader, EmptyState, Bandeau, Bouton } from '../../components/Ui';
import Crest from '../../components/Crest';
import Icon from '../../components/Icon';
import { colors, radius, spacing, type } from '../../theme';

const heure = (iso) => {
  try {
    const d = new Date(iso);
    return d.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' })
      + ' · ' + d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
  } catch (e) {
    return '';
  }
};

/**
 * Les matchs que l'operateur connecte peut saisir (FR-037).
 *
 * Chaque carte indique ou en est la preparation : tant que les deux
 * compositions ne sont pas enregistrees, c'est ce qu'il reste a faire avant le
 * coup d'envoi.
 */
export default function MesMatchsScreen({ navigation }) {
  const { operateur, fermerSession } = useOperateur();
  const enAttente = useFileEnAttente();
  const { data, loading, error } = usePolling(() => api.mesMatchs(), [], 30000);

  // Chaque passage sur cet ecran est une occasion de rattraper ce qui n'a pas
  // pu partir pendant une coupure reseau.
  useEffect(() => {
    viderFile().catch(() => {});
  }, []);

  const deconnecter = async () => {
    await fermerSession();
    navigation.replace('Connexion');
  };

  if (loading && !data) return <Loader />;

  if (error && !data) {
    return (
      <View style={{ flex: 1, backgroundColor: colors.cream, padding: spacing.lg }}>
        <EmptyState
          icon="info"
          title="Liste indisponible"
          subtitle="Impossible de joindre le serveur. Vérifiez votre connexion, la liste se rechargera toute seule."
        />
      </View>
    );
  }

  const matchs = Array.isArray(data) ? data : [];

  return (
    <ScrollView
      style={{ flex: 1, backgroundColor: colors.cream }}
      contentContainerStyle={{ padding: spacing.lg, gap: spacing.md }}
    >
      <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }}>
        <View>
          <Text style={type.h1}>Mes matchs</Text>
          <Text style={type.meta}>
            {operateur?.name}{operateur?.role ? ' · ' + operateur.role : ''}
          </Text>
        </View>
        <Pressable onPress={deconnecter} hitSlop={10}>
          <Text style={[type.kicker, { color: colors.brick }]}>Se déconnecter</Text>
        </Pressable>
      </View>

      {enAttente > 0 ? (
        <Bandeau ton="attente">
          {enAttente === 1
            ? '1 saisie en attente de réseau. Elle partira automatiquement.'
            : `${enAttente} saisies en attente de réseau. Elles partiront automatiquement.`}
        </Bandeau>
      ) : null}

      {matchs.length === 0 ? (
        <EmptyState
          icon="calendar"
          title="Aucun match à saisir"
          subtitle="Les rencontres qui vous sont confiées apparaîtront ici dès qu'elles seront programmées."
        />
      ) : (
        matchs.map((m) => {
          const enDirect = m.status === 'live' || m.status === 'halftime';
          return (
            <Card key={m.id} onPress={() => navigation.navigate('SaisieLive', { matchId: m.id })}>
              <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: spacing.sm }}>
                <Text style={[type.kicker, { color: colors.muted }]}>
                  {m.competition}{m.matchday ? ` · J${m.matchday}` : ''}{m.round_label ? ` · ${m.round_label}` : ''}
                </Text>
                {enDirect ? (
                  <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                    <View style={{ width: 7, height: 7, borderRadius: 4, backgroundColor: colors.live }} />
                    <Text style={[type.kicker, { color: colors.live }]}>En direct</Text>
                  </View>
                ) : null}
              </View>

              <View style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.md }}>
                <Crest team={{ id: m.home_id, name: m.home_name, abbr: m.home_abbr, logo: m.home_logo, color: m.home_color }} size={30} />
                <Text numberOfLines={1} style={[type.label, { flex: 1 }]}>{m.home_name}</Text>
                <Text style={[type.stat, { fontSize: 15 }]}>
                  {enDirect || m.status === 'finished' ? `${m.home_score ?? 0} - ${m.away_score ?? 0}` : 'vs'}
                </Text>
                <Text numberOfLines={1} style={[type.label, { flex: 1, textAlign: 'right' }]}>{m.away_name}</Text>
                <Crest team={{ id: m.away_id, name: m.away_name, abbr: m.away_abbr, logo: m.away_logo, color: m.away_color }} size={30} />
              </View>

              <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: spacing.md }}>
                <Icon name="clock" size={13} color={colors.faint} />
                <Text style={type.meta}>{heure(m.kickoff_at)}</Text>
                {m.venue ? (
                  <>
                    <Icon name="pin" size={13} color={colors.faint} />
                    <Text numberOfLines={1} style={[type.meta, { flex: 1 }]}>{m.venue}</Text>
                  </>
                ) : null}
              </View>

              <View style={{
                marginTop: spacing.md,
                paddingTop: spacing.md,
                borderTopWidth: 1,
                borderTopColor: colors.line,
                flexDirection: 'row',
                gap: spacing.sm,
              }}>
                <Bouton
                  variante={m.lineups_ready ? 'creux' : 'plein'}
                  style={{ flex: 1 }}
                  icone="user"
                  onPress={() => navigation.navigate('Composition', { matchId: m.id })}
                >
                  {m.lineups_ready ? 'Compositions' : 'Composer'}
                </Bouton>
                <Bouton
                  variante={enDirect ? 'plein' : 'creux'}
                  style={{ flex: 1 }}
                  icone="whistle"
                  onPress={() => navigation.navigate('SaisieLive', { matchId: m.id })}
                >
                  {enDirect ? 'Saisir' : 'Ouvrir'}
                </Bouton>
              </View>

              {!m.lineups_ready ? (
                <Text style={[type.meta, { marginTop: spacing.sm, color: colors.brick }]}>
                  Compositions à enregistrer avant le coup d'envoi.
                </Text>
              ) : null}
            </Card>
          );
        })
      )}
    </ScrollView>
  );
}
