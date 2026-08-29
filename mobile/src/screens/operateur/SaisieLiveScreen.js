import React, { useState, useEffect, useCallback } from 'react';
import { View, Text, ScrollView, Pressable, Alert } from 'react-native';

import { api, usePolling } from '../../api';
import { saisirEvenement, viderFile, useFileEnAttente } from '../../queue';
import { Card, Loader, EmptyState, Bandeau, Bouton } from '../../components/Ui';
import Icon from '../../components/Icon';
import { colors, fonts, radius, spacing, type } from '../../theme';

/**
 * Saisie d'un match en direct, depuis le bord du terrain (FR-039).
 *
 * Tout est pense pour une main et un pouce : cibles larges, choix en cascade
 * (fait de jeu, puis equipe, puis joueur), aucune saisie au clavier en dehors
 * de la minute. Une coupure reseau n'interrompt pas la saisie — l'evenement
 * part en file et sera transmis au retour de la connexion.
 */

const FAITS = [
  { type: 'goal', libelle: 'But', icone: 'trophy', demandePasseur: true },
  { type: 'penalty', libelle: 'Penalty', icone: 'trophy', demandePasseur: false },
  { type: 'own_goal', libelle: 'CSC', icone: 'swap', demandePasseur: false },
  { type: 'yellow', libelle: 'Carton jaune', icone: 'info', demandePasseur: false },
  { type: 'red', libelle: 'Carton rouge', icone: 'info', demandePasseur: false },
  { type: 'sub', libelle: 'Changement', icone: 'swap', demandePasseur: true },
];

const LIBELLES_FAIT = Object.fromEntries(FAITS.map((f) => [f.type, f.libelle]));

export default function SaisieLiveScreen({ route, navigation }) {
  const { matchId } = route.params;

  const { data: match, loading, error } = usePolling(() => api.match(matchId), [matchId], 15000);
  const { data: effectifs } = usePolling(() => api.effectifs(matchId), [matchId], 0);
  const enAttente = useFileEnAttente();

  const [minute, setMinute] = useState(0);
  const [etape, setEtape] = useState(null); // { fait, teamId, playerId }
  const [message, setMessage] = useState(null);
  const [envoi, setEnvoi] = useState(false);

  // La minute affichee suit le serveur tant que l'operateur n'y touche pas.
  useEffect(() => {
    if (match?.minute != null) setMinute(Number(match.minute));
  }, [match?.minute]);

  // Chaque cycle de rafraichissement est une chance de rattraper la file.
  useEffect(() => {
    const t = setInterval(() => { viderFile().catch(() => {}); }, 20000);
    return () => clearInterval(t);
  }, []);

  const majMatch = useCallback(async (champs) => {
    try {
      await api.majMatch(matchId, champs);
      setMessage(null);
    } catch (e) {
      setMessage({ ton: 'erreur', texte: e.message ?? 'Mise à jour impossible.' });
    }
  }, [matchId]);

  if (loading && !match) return <Loader />;

  if ((error && !match) || !match) {
    return (
      <View style={{ flex: 1, backgroundColor: colors.cream, padding: spacing.lg }}>
        <EmptyState
          icon="info"
          title="Match indisponible"
          subtitle="Impossible de charger la rencontre. Vérifiez votre connexion, l'écran se rechargera tout seul."
        />
      </View>
    );
  }

  const enDirect = match.status === 'live' || match.status === 'halftime';
  const termine = match.status === 'finished';

  const equipes = [
    { id: Number(match.home_team_id), nom: match.home_name, abbr: match.home_abbr, cote: 'home' },
    { id: Number(match.away_team_id), nom: match.away_name, abbr: match.away_abbr, cote: 'away' },
  ];

  const joueursDe = (teamId) => {
    const equipe = equipes.find((e) => e.id === teamId);
    return equipe && effectifs ? (effectifs[equipe.cote] ?? []) : [];
  };

  const envoyer = async (evenement) => {
    setEnvoi(true);
    try {
      const { transmis } = await saisirEvenement(matchId, { ...evenement, minute, is_published: 1 });
      setMessage(transmis
        ? { ton: 'succes', texte: 'Fait de jeu enregistré.' }
        : { ton: 'attente', texte: 'Pas de réseau : la saisie partira dès le retour de la connexion.' });
      setEtape(null);
    } catch (e) {
      setMessage({ ton: 'erreur', texte: e.message ?? 'Saisie refusée.' });
    } finally {
      setEnvoi(false);
    }
  };

  const supprimer = (evenement) => {
    Alert.alert(
      'Retirer ce fait de jeu ?',
      `${LIBELLES_FAIT[evenement.type] ?? evenement.type} à la ${evenement.minute}e. Le score et les statistiques seront recalculés.`,
      [
        { text: 'Annuler', style: 'cancel' },
        {
          text: 'Retirer',
          style: 'destructive',
          onPress: async () => {
            try {
              await api.supprimerEvenement(matchId, evenement.id);
              setMessage({ ton: 'succes', texte: 'Fait de jeu retiré, score recalculé.' });
            } catch (e) {
              setMessage({ ton: 'erreur', texte: e.message ?? 'Suppression impossible.' });
            }
          },
        },
      ]
    );
  };

  return (
    <View style={{ flex: 1, backgroundColor: colors.cream }}>
      {/* ------------------------------------------------ tableau d'affichage */}
      <View style={{ backgroundColor: colors.bordeaux, padding: spacing.lg, paddingBottom: spacing.md }}>
        <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }}>
          <Text numberOfLines={1} style={{ flex: 1, fontFamily: fonts.bodyBold, fontSize: 13, color: '#fff' }}>
            {match.home_abbr ?? match.home_name}
          </Text>
          <Text style={[type.score, { fontSize: 34 }]}>
            {match.home_score ?? 0} - {match.away_score ?? 0}
          </Text>
          <Text numberOfLines={1} style={{ flex: 1, textAlign: 'right', fontFamily: fonts.bodyBold, fontSize: 13, color: '#fff' }}>
            {match.away_abbr ?? match.away_name}
          </Text>
        </View>

        <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.md, marginTop: spacing.md }}>
          <Pressable
            onPress={() => setMinute((m) => Math.max(0, m - 1))}
            style={{ width: 44, height: 44, borderRadius: 22, backgroundColor: 'rgba(255,255,255,0.15)', alignItems: 'center', justifyContent: 'center' }}
          >
            <Text style={{ color: '#fff', fontFamily: fonts.bodyBold, fontSize: 20 }}>−</Text>
          </Pressable>
          <Pressable onPress={() => majMatch({ minute })} style={{ minWidth: 92, alignItems: 'center' }}>
            <Text style={{ fontFamily: fonts.display, fontSize: 30, color: '#fff' }}>{minute}&apos;</Text>
            <Text style={[type.kicker, { color: 'rgba(255,255,255,0.7)' }]}>Toucher pour publier</Text>
          </Pressable>
          <Pressable
            onPress={() => setMinute((m) => Math.min(200, m + 1))}
            style={{ width: 44, height: 44, borderRadius: 22, backgroundColor: 'rgba(255,255,255,0.15)', alignItems: 'center', justifyContent: 'center' }}
          >
            <Text style={{ color: '#fff', fontFamily: fonts.bodyBold, fontSize: 20 }}>+</Text>
          </Pressable>
        </View>
      </View>

      <ScrollView contentContainerStyle={{ padding: spacing.lg, gap: spacing.md, paddingBottom: 40 }}>
        {enAttente > 0 ? (
          <Bandeau ton="attente">
            {enAttente === 1
              ? '1 saisie attend le réseau.'
              : `${enAttente} saisies attendent le réseau.`} Elles partiront automatiquement.
          </Bandeau>
        ) : null}

        {message ? <Bandeau ton={message.ton}>{message.texte}</Bandeau> : null}

        {/* --------------------------------------------- pilotage du match */}
        <View style={{ flexDirection: 'row', gap: spacing.sm, flexWrap: 'wrap' }}>
          {match.status === 'scheduled' ? (
            <Bouton style={{ flex: 1 }} icone="whistle" onPress={() => majMatch({ status: 'live', minute: 0 })}>
              Coup d&apos;envoi
            </Bouton>
          ) : null}
          {match.status === 'live' ? (
            <>
              <Bouton style={{ flex: 1 }} variante="creux" onPress={() => majMatch({ status: 'halftime', minute: 45 })}>
                Mi-temps
              </Bouton>
              <Bouton style={{ flex: 1 }} variante="creux" onPress={() => majMatch({ status: 'finished' })}>
                Coup de sifflet final
              </Bouton>
            </>
          ) : null}
          {match.status === 'halftime' ? (
            <Bouton style={{ flex: 1 }} icone="play" onPress={() => majMatch({ status: 'live', minute: 46 })}>
              Reprise
            </Bouton>
          ) : null}
          {termine ? (
            <Bouton style={{ flex: 1 }} icone="chart" onPress={() => navigation.navigate('Cloture', { matchId })}>
              Renseigner l&apos;après-match
            </Bouton>
          ) : null}
        </View>

        {/* ------------------------------------------------ saisie en cascade */}
        {!termine ? (
          <Card>
            {!etape ? (
              <>
                <Text style={[type.h2, { marginBottom: spacing.md }]}>Fait de jeu</Text>
                <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm }}>
                  {FAITS.map((f) => (
                    <Bouton
                      key={f.type}
                      variante="creux"
                      icone={f.icone}
                      style={{ width: '48%' }}
                      onPress={() => setEtape({ fait: f })}
                    >
                      {f.libelle}
                    </Bouton>
                  ))}
                </View>
              </>
            ) : !etape.teamId ? (
              <>
                <EnTeteEtape titre={`${etape.fait.libelle} — quelle équipe ?`} onAnnuler={() => setEtape(null)} />
                {equipes.map((e) => (
                  <Bouton key={e.id} variante="creux" style={{ marginBottom: spacing.sm }}
                    onPress={() => setEtape({ ...etape, teamId: e.id })}>
                    {e.nom}
                  </Bouton>
                ))}
              </>
            ) : !etape.playerId ? (
              <>
                <EnTeteEtape
                  titre={etape.fait.type === 'sub' ? 'Qui entre ?' : 'Quel joueur ?'}
                  onAnnuler={() => setEtape({ fait: etape.fait })}
                />
                <ListeJoueurs
                  joueurs={joueursDe(etape.teamId)}
                  onChoisir={(j) => {
                    if (etape.fait.demandePasseur) setEtape({ ...etape, playerId: j.id });
                    else envoyer({ type: etape.fait.type, team_id: etape.teamId, player_id: j.id });
                  }}
                />
              </>
            ) : (
              <>
                <EnTeteEtape
                  titre={etape.fait.type === 'sub' ? 'Qui sort ?' : 'Passeur décisif ?'}
                  onAnnuler={() => setEtape({ fait: etape.fait, teamId: etape.teamId })}
                />
                <Bouton
                  variante="creux"
                  style={{ marginBottom: spacing.sm }}
                  disabled={envoi}
                  onPress={() => envoyer({ type: etape.fait.type, team_id: etape.teamId, player_id: etape.playerId })}
                >
                  {etape.fait.type === 'sub' ? 'Ne pas préciser' : 'Aucun passeur'}
                </Bouton>
                <ListeJoueurs
                  joueurs={joueursDe(etape.teamId).filter((j) => j.id !== etape.playerId)}
                  onChoisir={(j) => envoyer({
                    type: etape.fait.type,
                    team_id: etape.teamId,
                    player_id: etape.playerId,
                    related_player_id: j.id,
                  })}
                />
              </>
            )}
          </Card>
        ) : null}

        {/* ------------------------------------------------------ fil du match */}
        <Text style={[type.h2, { marginTop: spacing.sm }]}>Fil du match</Text>
        {(match.events ?? []).length === 0 ? (
          <EmptyState
            icon="clock"
            title="Aucun fait de jeu"
            subtitle="Les buts, cartons et changements que vous saisirez apparaîtront ici."
          />
        ) : (
          (match.events ?? []).map((ev) => (
            <Card key={ev.id}>
              <View style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.md }}>
                <Text style={{ width: 34, fontFamily: fonts.display, fontSize: 16, color: colors.bordeaux }}>
                  {ev.minute}&apos;
                </Text>
                <View style={{ flex: 1 }}>
                  <Text style={type.label}>
                    {LIBELLES_FAIT[ev.type] ?? ev.type}
                    {ev.team_abbr ? ` · ${ev.team_abbr}` : ''}
                  </Text>
                  {ev.player ? <Text style={type.meta}>{ev.player}</Text> : null}
                  {ev.related_player ? <Text style={type.meta}>avec {ev.related_player}</Text> : null}
                </View>
                <Pressable onPress={() => supprimer(ev)} hitSlop={12} style={{ padding: 6 }}>
                  <Text style={[type.kicker, { color: colors.red }]}>Retirer</Text>
                </Pressable>
              </View>
            </Card>
          ))
        )}
      </ScrollView>
    </View>
  );
}

function EnTeteEtape({ titre, onAnnuler }) {
  return (
    <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: spacing.md }}>
      <Text style={[type.h2, { flex: 1 }]}>{titre}</Text>
      <Pressable onPress={onAnnuler} hitSlop={12}>
        <Text style={[type.kicker, { color: colors.brick }]}>Retour</Text>
      </Pressable>
    </View>
  );
}

function ListeJoueurs({ joueurs, onChoisir }) {
  if (!joueurs || joueurs.length === 0) {
    return (
      <Text style={[type.meta, { paddingVertical: spacing.md }]}>
        Aucun joueur dans cet effectif. Enregistrez d&apos;abord la composition.
      </Text>
    );
  }
  return (
    <View style={{ gap: spacing.sm }}>
      {joueurs.map((j) => (
        <Pressable
          key={j.id}
          onPress={() => onChoisir(j)}
          style={{
            flexDirection: 'row',
            alignItems: 'center',
            gap: spacing.md,
            minHeight: 52,
            paddingHorizontal: spacing.md,
            borderRadius: radius.md,
            borderWidth: 1,
            borderColor: colors.line,
            backgroundColor: colors.card,
          }}
        >
          <Text style={{ width: 26, fontFamily: fonts.display, fontSize: 15, color: colors.bordeaux }}>
            {j.jersey_number ?? '—'}
          </Text>
          <Text numberOfLines={1} style={[type.label, { flex: 1 }]}>{j.name}</Text>
          <Icon name="chevron" size={15} color={colors.faint} />
        </Pressable>
      ))}
    </View>
  );
}
