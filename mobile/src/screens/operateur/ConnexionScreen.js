import React, { useState } from 'react';
import { View, Text, ScrollView, KeyboardAvoidingView, Platform } from 'react-native';

import { api } from '../../api';
import { useOperateur } from '../../auth';
import { Bouton, Champ, Bandeau } from '../../components/Ui';
import Icon from '../../components/Icon';
import { colors, spacing, type } from '../../theme';

/**
 * Connexion de l'operateur de saisie (FR-036).
 *
 * Cet ecran n'existe que pour les commissaires, commentateurs et
 * organisateurs. La consultation publique n'en depend jamais : un supporter
 * utilise toute l'application sans compte.
 */
export default function ConnexionScreen({ navigation }) {
  const { ouvrirSession } = useOperateur();
  const [email, setEmail] = useState('');
  const [motDePasse, setMotDePasse] = useState('');
  const [erreur, setErreur] = useState(null);
  const [enCours, setEnCours] = useState(false);

  const seConnecter = async () => {
    if (!email.trim() || !motDePasse) {
      setErreur('Renseignez votre adresse et votre mot de passe.');
      return;
    }
    setEnCours(true);
    setErreur(null);
    try {
      const session = await api.connexion(email.trim(), motDePasse);
      await ouvrirSession(session);
      navigation.replace('MesMatchs');
    } catch (e) {
      setErreur(e.message ?? 'La connexion a echoue.');
    } finally {
      setEnCours(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={{ flex: 1, backgroundColor: colors.cream }}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <ScrollView contentContainerStyle={{ padding: spacing.lg, paddingTop: spacing.xxl }}>
        <View style={{ alignItems: 'center', marginBottom: spacing.xl, gap: spacing.md }}>
          <Icon name="whistle" size={34} color={colors.bordeaux} />
          <Text style={type.h1}>Espace opérateur</Text>
          <Text style={[type.meta, { textAlign: 'center', paddingHorizontal: spacing.xl }]}>
            Réservé aux commissaires, commentateurs et organisateurs chargés de
            saisir les rencontres.
          </Text>
        </View>

        {erreur ? <Bandeau ton="erreur">{erreur}</Bandeau> : null}

        <Champ
          label="Adresse électronique"
          valeur={email}
          onChange={setEmail}
          placeholder="vous@exemple.cm"
          autoCapitalize="none"
          keyboardType="email-address"
          autoComplete="email"
        />
        <Champ
          label="Mot de passe"
          valeur={motDePasse}
          onChange={setMotDePasse}
          placeholder="Votre mot de passe"
          secureTextEntry
          autoComplete="password"
        />

        <Bouton onPress={seConnecter} disabled={enCours} icone="user">
          {enCours ? 'Connexion…' : 'Se connecter'}
        </Bouton>

        <Text style={[type.meta, { textAlign: 'center', marginTop: spacing.xl }]}>
          Vous n'avez pas de compte ? Rapprochez-vous de l'organisation du Garoua
          Football Challenge.
        </Text>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
