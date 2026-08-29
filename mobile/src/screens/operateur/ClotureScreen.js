import React, { useState, useEffect } from 'react';
import { View, Text, ScrollView, KeyboardAvoidingView, Platform } from 'react-native';

import { api, useQuery } from '../../api';
import { Card, Loader, EmptyState, Bandeau, Bouton, Champ, Segmented } from '../../components/Ui';
import { colors, spacing, type } from '../../theme';

/**
 * Apres-match : affluence, statistiques de rencontre et cloture (FR-040).
 *
 * Ces chiffres sont les seuls du produit qui ne se deduisent d'aucun evenement
 * — possession, tirs, corners, fautes, hors-jeu se relevent a la main. Le
 * score, lui, n'apparait pas ici : il vient des faits de jeu et personne ne le
 * saisit (invariant I1).
 */
const CHAMPS = [
  { cle: 'possession', libelle: 'Possession (%)' },
  { cle: 'shots', libelle: 'Tirs' },
  { cle: 'shots_on_target', libelle: 'Tirs cadrés' },
  { cle: 'corners', libelle: 'Corners' },
  { cle: 'fouls', libelle: 'Fautes' },
  { cle: 'offsides', libelle: 'Hors-jeu' },
];

const vide = () => Object.fromEntries(CHAMPS.map((c) => [c.cle, '']));

export default function ClotureScreen({ route, navigation }) {
  const { matchId } = route.params;
  const { data: match, loading, error } = useQuery(() => api.match(matchId), [matchId]);

  const [cote, setCote] = useState('home');
  const [saisie, setSaisie] = useState({ home: vide(), away: vide() });
  const [affluence, setAffluence] = useState('');
  const [message, setMessage] = useState(null);
  const [enCours, setEnCours] = useState(false);

  // Reprend ce qui existe deja, pour que revenir sur l'ecran ne fasse pas
  // repartir de zero.
  useEffect(() => {
    if (!match) return;
    if (match.attendance != null) setAffluence(String(match.attendance));
    const repris = { home: vide(), away: vide() };
    (match.stats ?? []).forEach((s) => {
      const c = Number(s.team_id) === Number(match.home_team_id) ? 'home' : 'away';
      CHAMPS.forEach(({ cle }) => {
        if (s[cle] != null) repris[c][cle] = String(s[cle]);
      });
    });
    setSaisie(repris);
  }, [match]);

  if (loading && !match) return <Loader />;

  if (error || !match) {
    return (
      <View style={{ flex: 1, backgroundColor: colors.cream, padding: spacing.lg }}>
        <EmptyState
          icon="info"
          title="Match indisponible"
          subtitle="Impossible de charger la rencontre. Vérifiez votre connexion et réessayez."
        />
      </View>
    );
  }

  const equipe = cote === 'home'
    ? { id: Number(match.home_team_id), nom: match.home_name }
    : { id: Number(match.away_team_id), nom: match.away_name };

  const majChamp = (cle, valeur) => {
    // Chiffres uniquement : ces champs ne recoivent que des entiers.
    const propre = valeur.replace(/[^0-9]/g, '');
    setSaisie((p) => ({ ...p, [cote]: { ...p[cote], [cle]: propre } }));
    setMessage(null);
  };

  const enregistrer = async () => {
    setEnCours(true);
    setMessage(null);
    try {
      if (affluence !== '') {
        await api.majMatch(matchId, { attendance: Number(affluence) });
      }
      const valeurs = Object.fromEntries(
        Object.entries(saisie[cote])
          .filter(([, v]) => v !== '')
          .map(([k, v]) => [k, Number(v)])
      );
      if (Object.keys(valeurs).length > 0) {
        await api.enregistrerStats(matchId, equipe.id, valeurs);
      }
      setMessage({ ton: 'succes', texte: `Statistiques de ${equipe.nom} enregistrées.` });
    } catch (e) {
      setMessage({ ton: 'erreur', texte: e.message ?? 'Enregistrement impossible.' });
    } finally {
      setEnCours(false);
    }
  };

  const cloturer = async () => {
    setEnCours(true);
    try {
      await api.majMatch(matchId, { status: 'finished' });
      setMessage({ ton: 'succes', texte: 'Match clôturé. Le classement est à jour.' });
      navigation.navigate('MesMatchs');
    } catch (e) {
      setMessage({ ton: 'erreur', texte: e.message ?? 'Clôture impossible.' });
    } finally {
      setEnCours(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={{ flex: 1, backgroundColor: colors.cream }}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <ScrollView contentContainerStyle={{ padding: spacing.lg, gap: spacing.md, paddingBottom: 60 }}>
        <Text style={type.h1}>Après-match</Text>
        <Text style={type.meta}>
          {match.home_name} {match.home_score ?? 0} - {match.away_score ?? 0} {match.away_name}
        </Text>

        {message ? <Bandeau ton={message.ton}>{message.texte}</Bandeau> : null}

        <Card>
          <Champ
            label="Affluence (nombre de spectateurs)"
            valeur={affluence}
            onChange={(v) => setAffluence(v.replace(/[^0-9]/g, ''))}
            placeholder="Par exemple 1200"
            keyboardType="number-pad"
          />
        </Card>

        <Segmented
          value={cote}
          onChange={(v) => { setCote(v); setMessage(null); }}
          options={[
            { value: 'home', label: match.home_abbr ?? 'Recevant' },
            { value: 'away', label: match.away_abbr ?? 'Visiteur' },
          ]}
        />

        <Card>
          <Text style={[type.h2, { marginBottom: spacing.md }]}>{equipe.nom}</Text>
          {CHAMPS.map((c) => (
            <Champ
              key={c.cle}
              label={c.libelle}
              valeur={saisie[cote][c.cle]}
              onChange={(v) => majChamp(c.cle, v)}
              placeholder="—"
              keyboardType="number-pad"
            />
          ))}
        </Card>

        <Bouton onPress={enregistrer} disabled={enCours} icone="chart">
          {enCours ? 'Enregistrement…' : 'Enregistrer les statistiques'}
        </Bouton>

        {match.status !== 'finished' ? (
          <>
            <Bandeau ton="info">
              La clôture fait entrer le résultat au classement. Vérifiez le score
              et le fil du match avant de clôturer.
            </Bandeau>
            <Bouton variante="danger" onPress={cloturer} disabled={enCours} icone="whistle">
              Clôturer le match
            </Bouton>
          </>
        ) : (
          <Bandeau ton="succes">
            Match clôturé : le résultat est pris en compte au classement.
          </Bandeau>
        )}
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
