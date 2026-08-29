import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import * as SecureStore from 'expo-secure-store';

/**
 * Session de l'opérateur de saisie (commissaire, commentateur, organisateur).
 *
 * Le jeton vit dans le stockage sécurisé de l'appareil, jamais dans
 * AsyncStorage qui est en clair (FR-042). La consultation publique n'utilise
 * rien de tout ceci : un supporter n'a pas de compte.
 */
const CLE_JETON = 'gfc_operateur_jeton';
const CLE_PROFIL = 'gfc_operateur_profil';

let jetonMemoire = null;

/** Jeton courant, lu depuis le stockage sécurisé au premier appel. */
export async function jetonCourant() {
  if (jetonMemoire) return jetonMemoire;
  try {
    const brut = await SecureStore.getItemAsync(CLE_JETON);
    if (!brut) return null;
    const { jeton, expireLe } = JSON.parse(brut);
    if (expireLe && new Date(expireLe) <= new Date()) {
      await oublierSession();
      return null;
    }
    jetonMemoire = jeton;
    return jeton;
  } catch (e) {
    return null;
  }
}

export async function memoriserSession(jeton, expireLe, profil) {
  jetonMemoire = jeton;
  await SecureStore.setItemAsync(CLE_JETON, JSON.stringify({ jeton, expireLe }));
  await SecureStore.setItemAsync(CLE_PROFIL, JSON.stringify(profil ?? null));
}

export async function oublierSession() {
  jetonMemoire = null;
  await SecureStore.deleteItemAsync(CLE_JETON).catch(() => {});
  await SecureStore.deleteItemAsync(CLE_PROFIL).catch(() => {});
}

async function profilMemorise() {
  try {
    const brut = await SecureStore.getItemAsync(CLE_PROFIL);
    return brut ? JSON.parse(brut) : null;
  } catch (e) {
    return null;
  }
}

const ContexteOperateur = createContext({
  operateur: null,
  pret: false,
  ouvrirSession: async () => {},
  fermerSession: async () => {},
});

export function FournisseurOperateur({ children }) {
  const [operateur, setOperateur] = useState(null);
  const [pret, setPret] = useState(false);

  useEffect(() => {
    let vivant = true;
    (async () => {
      const jeton = await jetonCourant();
      const profil = jeton ? await profilMemorise() : null;
      if (vivant) {
        setOperateur(profil);
        setPret(true);
      }
    })();
    return () => {
      vivant = false;
    };
  }, []);

  const ouvrirSession = useCallback(async (session) => {
    await memoriserSession(session.token, session.expires_at, session.user);
    setOperateur(session.user);
  }, []);

  const fermerSession = useCallback(async () => {
    await oublierSession();
    setOperateur(null);
  }, []);

  return (
    <ContexteOperateur.Provider value={{ operateur, pret, ouvrirSession, fermerSession }}>
      {children}
    </ContexteOperateur.Provider>
  );
}

export function useOperateur() {
  return useContext(ContexteOperateur);
}
