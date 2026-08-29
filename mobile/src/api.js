import { useState, useEffect, useRef } from 'react';
import { AppState } from 'react-native';
import Constants from 'expo-constants';
import AsyncStorage from '@react-native-async-storage/async-storage';

import { jetonCourant, oublierSession } from './auth';

const BASE = Constants.expoConfig?.extra?.apiUrl ?? 'https://gfc.trugroup.cm/api';

/** Cache mémoire court : évite de retélécharger une liste en navigant. */
const TTL = 60 * 1000;
const memoire = new Map();

/**
 * Marque des données servies depuis le cache local faute de réseau.
 *
 * L'application doit afficher le dernier contenu connu plutôt qu'une erreur
 * (FR-031), mais elle doit dire qu'il n'est pas à jour. Le drapeau voyage sur
 * l'objet retourné : les écrans le lisent, sans avoir à changer de signature.
 */
function marquerPerime(data) {
  if (data && typeof data === 'object') {
    try {
      Object.defineProperty(data, '__perime', { value: true, enumerable: false });
    } catch (e) {
      // objet gelé : tant pis pour le drapeau, les données restent utiles
    }
  }
  return data;
}

export function estPerime(data) {
  return Boolean(data && data.__perime);
}

/** Lecture publique, avec cache mémoire puis repli sur le disque. */
async function lire(path, { cache = true } = {}) {
  const cle = 'gfc:' + path;

  if (cache) {
    const hit = memoire.get(cle);
    if (hit && Date.now() - hit.at < TTL) return hit.data;
  }

  try {
    const res = await fetch(BASE + path, { headers: { Accept: 'application/json' } });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();
    memoire.set(cle, { at: Date.now(), data });
    await AsyncStorage.setItem(cle, JSON.stringify(data));
    return data;
  } catch (err) {
    const stocke = await AsyncStorage.getItem(cle);
    if (stocke) return marquerPerime(JSON.parse(stocke));
    throw err;
  }
}

/**
 * Écriture authentifiée. Ne met jamais en cache et ne se replie jamais sur le
 * disque : une écriture qui n'a pas atteint le serveur n'a pas eu lieu, c'est
 * la file d'attente qui la reprendra.
 */
async function ecrire(path, method, corps) {
  const jeton = await jetonCourant();
  if (!jeton) {
    const e = new Error('Vous devez vous connecter.');
    e.code = 'non_authentifie';
    throw e;
  }

  const res = await fetch(BASE + path, {
    method,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      Authorization: 'Bearer ' + jeton,
    },
    body: corps ? JSON.stringify(corps) : undefined,
  });

  const data = await res.json().catch(() => null);

  if (res.status === 401) {
    // Jeton expiré ou révoqué : l'opérateur doit se reconnecter.
    await oublierSession();
    const e = new Error(data?.error?.message ?? 'Session expirée, reconnectez-vous.');
    e.code = 'session_expiree';
    e.status = 401;
    throw e;
  }

  if (!res.ok) {
    const e = new Error(data?.error?.message ?? 'La requête a échoué.');
    e.code = data?.error?.code ?? 'erreur';
    e.status = res.status;
    throw e;
  }

  return data;
}

export const api = {
  // ------------------------------------------------ consultation publique
  competitions: () => lire('/competitions'),
  news: (limit = 20) => lire('/news?limit=' + limit),
  media: (type) => lire('/media' + (type ? '?type=' + type : '')),
  fixtures: (competition) =>
    lire('/matches?scope=upcoming' + (competition ? '&competition=' + competition : '')),
  results: (competition) =>
    lire('/matches?scope=results' + (competition ? '&competition=' + competition : '')),
  match: (id) => lire('/matches/' + id, { cache: false }),
  standings: (competition = 'championnat') => lire('/standings?competition=' + competition),
  teams: () => lire('/teams'),
  team: (id) => lire('/teams/' + id),
  player: (id) => lire('/players/' + id),
  playerStats: (metric = 'goals', competition = 'championnat') =>
    lire('/stats/players?metric=' + metric + '&competition=' + competition),
  teamStats: (competition = 'championnat') => lire('/stats/teams?competition=' + competition),

  registerDevice: (expoToken, platform, favouriteTeamId) =>
    fetch(BASE + '/devices', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        expo_token: expoToken,
        platform,
        favourite_team_id: favouriteTeamId ?? null,
      }),
    }),

  // ------------------------------------------------- espace opérateur (US8)
  connexion: async (email, motDePasse) => {
    const res = await fetch(BASE + '/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email, password: motDePasse }),
    });
    const data = await res.json().catch(() => null);
    if (!res.ok) {
      throw new Error(data?.error?.message ?? 'Identifiants invalides.');
    }
    return data;
  },

  mesMatchs: () => ecrire('/me/matches', 'GET'),
  effectifs: (matchId) => ecrire('/matches/' + matchId + '/squads', 'GET'),

  enregistrerComposition: (matchId, teamId, joueurs) =>
    ecrire('/matches/' + matchId + '/lineups', 'PUT', { team_id: teamId, players: joueurs }),

  enregistrerStats: (matchId, teamId, stats) =>
    ecrire('/matches/' + matchId + '/stats', 'PUT', { team_id: teamId, ...stats }),

  ajouterEvenement: (matchId, evenement) =>
    ecrire('/matches/' + matchId + '/events', 'POST', evenement),

  supprimerEvenement: (matchId, eventId) =>
    ecrire('/matches/' + matchId + '/events/' + eventId, 'DELETE'),

  majMatch: (matchId, champs) => ecrire('/matches/' + matchId, 'PATCH', champs),
};

/**
 * Rafraîchissement automatique pendant un match en direct.
 *
 * Le cycle s'arrête dès que l'application passe en arrière-plan : inutile de
 * consommer la batterie et les données mobiles d'un supporter pour un écran
 * que personne ne regarde. Il redémarre au retour au premier plan.
 */
export function usePolling(fn, deps, intervalMs = 15000) {
  const [state, setState] = useState({ data: null, loading: true, error: null, perime: false });
  const fnRef = useRef(fn);
  fnRef.current = fn;

  useEffect(() => {
    let vivant = true;
    let timer = null;

    const charger = async () => {
      try {
        const data = await fnRef.current();
        if (vivant) {
          setState({ data, loading: false, error: null, perime: estPerime(data) });
        }
      } catch (error) {
        // On garde à l'écran ce qu'on avait : mieux vaut une donnée d'il y a
        // deux minutes qu'un écran d'erreur (principe IV).
        if (vivant) setState((s) => ({ ...s, loading: false, error }));
      }
      if (vivant && intervalMs && AppState.currentState === 'active') {
        timer = setTimeout(charger, intervalMs);
      }
    };

    const surChangementEtat = (etat) => {
      if (etat === 'active' && vivant && intervalMs && !timer) charger();
      if (etat !== 'active' && timer) {
        clearTimeout(timer);
        timer = null;
      }
    };

    charger();
    const sub = AppState.addEventListener('change', surChangementEtat);

    return () => {
      vivant = false;
      if (timer) clearTimeout(timer);
      sub.remove();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, deps);

  return state;
}

/** Lecture unique, sans rafraîchissement. */
export function useQuery(fn, deps = []) {
  return usePolling(fn, deps, 0);
}
