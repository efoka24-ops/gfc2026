import React, { useState, useEffect, useMemo } from 'react';
import { View, Text, ScrollView, Pressable } from 'react-native';

import { api, useQuery } from '../../api';
import { Card, Loader, EmptyState, Bandeau, Bouton, Segmented } from '../../components/Ui';
import Icon from '../../components/Icon';
import { colors, fonts, radius, spacing, type } from '../../theme';

const POSTES = { GB: 'Gardiens', DEF: 'Défenseurs', MIL: 'Milieux', ATT: 'Attaquants' };

/**
 * Composition d'avant-match (FR-038).
 *
 * L'operateur choisit les titulaires et les remplacants parmi le seul effectif
 * de l'equipe concernee : c'est ce qui rend impossible d'aligner un joueur
 * d'un autre club, et de fausser ensuite les statistiques.
 */
export default function CompositionScreen({ route, navigation }) {
  const { matchId } = route.params;

  const { data: match, loading: chargeMatch } = useQuery(() => api.match(matchId), [matchId]);
  const { data: effectifs, loading: chargeEffectifs, error } = useQuery(
    () => api.effectifs(matchId),
    [matchId]
  );

  const [cote, setCote] = useState('home');
  const [choix, setChoix] = useState({ home: {}, away: {} });
  const [message, setMessage] = useState(null);
  const [enCours, setEnCours] = useState(false);

  // Reprend ce qui a deja ete enregistre : revenir sur l'ecran ne doit pas
  // obliger a tout resaisir.
  useEffect(() => {
    if (!match?.lineups?.length) return;
    const repris = { home: {}, away: {} };
    match.lineups.forEach((l) => {
      const c = Number(l.team_id) === Number(match.home_team_id) ? 'home' : 'away';
      repris[c][l.player_id] = l.is_starter ? 'titulaire' : 'remplacant';
    });
    setChoix(repris);
  }, [match]);

  const equipe = useMemo(() => {
    if (!match) return null;
    return cote === 'home'
      ? { id: Number(match.home_team_id), nom: match.home_name }
      : { id: Number(match.away_team_id), nom: match.away_name };
  }, [match, cote]);

  if (chargeMatch || chargeEffectifs) return <Loader />;

  if (error || !effectifs || !match) {
    return (
      <View style={{ flex: 1, backgroundColor: colors.cream, padding: spacing.lg }}>
        <EmptyState
          icon="info"
          title="Effectifs indisponibles"
          subtitle="Impossible de charger les effectifs. Vérifiez votre connexion et réessayez."
        />
      </View>
    );
  }

  const joueurs = effectifs[cote] ?? [];
  const selection = choix[cote] ?? {};
  const titulaires = Object.values(selection).filter((v) => v === 'titulaire').length;
  const remplacants = Object.values(selection).filter((v) => v === 'remplacant').length;

  const basculer = (playerId) => {
    setChoix((prec) => {
      const actuel = prec[cote]?.[playerId];
      const suivant = actuel === 'titulaire' ? 'remplacant' : actuel === 'remplacant' ? undefined : 'titulaire';
      const copie = { ...(prec[cote] ?? {}) };
      if (suivant) copie[playerId] = suivant;
      else delete copie[playerId];
      return { ...prec, [cote]: copie };
    });
    setMessage(null);
  };

  const enregistrer = async () => {
    if (titulaires === 0) {
      setMessage({ ton: 'erreur', texte: 'Choisissez au moins un titulaire avant d\'enregistrer.' });
      return;
    }
    setEnCours(true);
    setMessage(null);
    try {
      await api.enregistrerComposition(
        matchId,
        equipe.id,
        Object.entries(selection).map(([playerId, role]) => ({
          player_id: Number(playerId),
          is_starter: role === 'titulaire',
        }))
      );
      setMessage({ ton: 'succes', texte: `Composition de ${equipe.nom} enregistrée.` });
    } catch (e) {
      setMessage({ ton: 'erreur', texte: e.message ?? 'Enregistrement impossible.' });
    } finally {
      setEnCours(false);
    }
  };

  const parPoste = joueurs.reduce((acc, j) => {
    (acc[j.position] ??= []).push(j);
    return acc;
  }, {});

  return (
    <View style={{ flex: 1, backgroundColor: colors.cream }}>
      <ScrollView contentContainerStyle={{ padding: spacing.lg, paddingBottom: 120, gap: spacing.md }}>
        <Text style={type.h1}>Composition</Text>
        <Text style={type.meta}>{match.home_name} contre {match.away_name}</Text>

        <Segmented
          value={cote}
          onChange={(v) => { setCote(v); setMessage(null); }}
          options={[
            { value: 'home', label: match.home_abbr ?? 'Recevant' },
            { value: 'away', label: match.away_abbr ?? 'Visiteur' },
          ]}
        />

        {message ? <Bandeau ton={message.ton}>{message.texte}</Bandeau> : null}

        <Bandeau ton="info">
          Appuyez une fois pour un titulaire, deux fois pour un remplaçant, trois
          fois pour retirer le joueur.
        </Bandeau>

        {joueurs.length === 0 ? (
          <EmptyState
            icon="user"
            title="Effectif vide"
            subtitle="Aucun joueur n'est enregistré pour cette équipe. L'organisation doit d'abord saisir l'effectif."
          />
        ) : (
          Object.entries(POSTES).map(([code, libelle]) => {
            const liste = parPoste[code] ?? [];
            if (liste.length === 0) return null;
            return (
              <View key={code} style={{ gap: spacing.sm }}>
                <Text style={[type.kicker, { color: colors.muted, marginTop: spacing.sm }]}>{libelle}</Text>
                {liste.map((j) => {
                  const role = selection[j.id];
                  return (
                    <Pressable
                      key={j.id}
                      onPress={() => basculer(j.id)}
                      style={{
                        flexDirection: 'row',
                        alignItems: 'center',
                        gap: spacing.md,
                        minHeight: 52,
                        paddingHorizontal: spacing.md,
                        borderRadius: radius.md,
                        backgroundColor: role === 'titulaire' ? colors.bordeaux
                          : role === 'remplacant' ? '#F2E3D2' : colors.card,
                        borderWidth: 1,
                        borderColor: colors.line,
                      }}
                    >
                      <Text style={{
                        width: 26,
                        fontFamily: fonts.display,
                        fontSize: 15,
                        color: role === 'titulaire' ? '#fff' : colors.bordeaux,
                      }}>
                        {j.jersey_number ?? '—'}
                      </Text>
                      <Text numberOfLines={1} style={{
                        flex: 1,
                        fontFamily: fonts.bodyBold,
                        fontSize: 13.5,
                        color: role === 'titulaire' ? '#fff' : colors.ink,
                      }}>
                        {j.name}
                      </Text>
                      {role ? (
                        <Text style={{
                          fontFamily: fonts.bodyBold,
                          fontSize: 10,
                          letterSpacing: 1,
                          textTransform: 'uppercase',
                          color: role === 'titulaire' ? '#fff' : '#8A5A12',
                        }}>
                          {role === 'titulaire' ? 'Titulaire' : 'Remplaçant'}
                        </Text>
                      ) : null}
                    </Pressable>
                  );
                })}
              </View>
            );
          })
        )}
      </ScrollView>

      <View style={{
        position: 'absolute',
        left: 0, right: 0, bottom: 0,
        padding: spacing.lg,
        paddingTop: spacing.md,
        backgroundColor: colors.cream,
        borderTopWidth: 1,
        borderTopColor: colors.line,
      }}>
        <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: spacing.sm }}>
          <Text style={type.meta}>{titulaires} titulaire{titulaires > 1 ? 's' : ''}</Text>
          <Text style={type.meta}>{remplacants} remplaçant{remplacants > 1 ? 's' : ''}</Text>
        </View>
        <Bouton onPress={enregistrer} disabled={enCours} icone="shield">
          {enCours ? 'Enregistrement…' : `Enregistrer ${equipe?.nom ?? ''}`}
        </Bouton>
      </View>
    </View>
  );
}
