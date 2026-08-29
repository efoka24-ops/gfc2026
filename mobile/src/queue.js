import { useState, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';

import { api } from './api';

/**
 * File d'attente des saisies faites hors réseau (FR-041).
 *
 * Au bord du terrain de Roumdé Adjia, le réseau tombe. Un but saisi pendant une
 * coupure ne doit ni être perdu, ni compté deux fois quand la connexion
 * revient. Chaque événement part donc avec un `client_ref` unique généré ici :
 * le serveur, qui porte une contrainte d'unicité sur (match_id, client_ref),
 * renvoie l'événement déjà enregistré au lieu d'en créer un second.
 *
 * La minute conservée est celle de la saisie, pas celle de la transmission :
 * un but marqué à la 63e reste à la 63e même s'il ne part qu'à la 70e.
 */
const CLE_FILE = 'gfc:file_saisie';

let ecouteurs = [];

function prevenir(file) {
  ecouteurs.forEach((fn) => fn(file));
}

/** Identifiant unique de saisie. Pas de dépendance : uuid v4 en 30 lignes. */
export function nouvelleReference() {
  const hex = '0123456789abcdef';
  let s = '';
  for (let i = 0; i < 36; i += 1) {
    if (i === 8 || i === 13 || i === 18 || i === 23) {
      s += '-';
    } else if (i === 14) {
      s += '4';
    } else if (i === 19) {
      s += hex[(Math.floor(Math.random() * 4) + 8)];
    } else {
      s += hex[Math.floor(Math.random() * 16)];
    }
  }
  return s;
}

export async function lireFile() {
  try {
    const brut = await AsyncStorage.getItem(CLE_FILE);
    return brut ? JSON.parse(brut) : [];
  } catch (e) {
    return [];
  }
}

async function ecrireFile(file) {
  await AsyncStorage.setItem(CLE_FILE, JSON.stringify(file));
  prevenir(file);
}

/**
 * Enregistre un événement. Tente l'envoi immédiat ; en cas d'échec réseau, le
 * met en file et rend la main tout de suite — l'opérateur ne doit jamais
 * attendre le réseau pour saisir le but suivant.
 *
 * @returns {Promise<{transmis: boolean, match?: object}>}
 */
export async function saisirEvenement(matchId, evenement) {
  const aTransmettre = {
    ...evenement,
    client_ref: evenement.client_ref ?? nouvelleReference(),
  };

  try {
    const reponse = await api.ajouterEvenement(matchId, aTransmettre);
    return { transmis: true, match: reponse?.match };
  } catch (err) {
    // Une donnée refusée par le serveur (422) ou une session expirée ne se
    // règleront pas en réessayant : inutile de les mettre en file.
    if (err.status === 422 || err.status === 401) throw err;

    const file = await lireFile();
    file.push({ matchId, evenement: aTransmettre, saisiLe: new Date().toISOString() });
    await ecrireFile(file);
    return { transmis: false };
  }
}

/**
 * Tente de transmettre tout ce qui attend. Appelée au retour du réseau et à
 * chaque cycle de rafraîchissement de l'écran de saisie.
 *
 * @returns {Promise<{transmis: number, restants: number}>}
 */
export async function viderFile() {
  const file = await lireFile();
  if (file.length === 0) return { transmis: 0, restants: 0 };

  const restants = [];
  let transmis = 0;

  for (const entree of file) {
    try {
      await api.ajouterEvenement(entree.matchId, entree.evenement);
      transmis += 1;
    } catch (err) {
      if (err.status === 422) {
        // Le serveur refuse définitivement cette saisie : la garder en file la
        // ferait échouer indéfiniment. On l'abandonne, elle est journalisée.
        // eslint-disable-next-line no-console
        console.warn('Saisie refusée par le serveur, retirée de la file', entree, err.message);
        continue;
      }
      restants.push(entree);
    }
  }

  await ecrireFile(restants);
  return { transmis, restants: restants.length };
}

export async function viderCompletement() {
  await ecrireFile([]);
}

/** Nombre de saisies en attente, pour l'afficher à l'opérateur. */
export function useFileEnAttente() {
  const [enAttente, setEnAttente] = useState(0);

  useEffect(() => {
    let vivant = true;

    const majDepuis = (file) => {
      if (vivant) setEnAttente(file.length);
    };

    lireFile().then(majDepuis);
    ecouteurs.push(majDepuis);

    return () => {
      vivant = false;
      ecouteurs = ecouteurs.filter((fn) => fn !== majDepuis);
    };
  }, []);

  return enAttente;
}
