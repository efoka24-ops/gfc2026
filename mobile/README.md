# Application mobile — Garoua Football Challenge

App React Native (Expo) en français. Aucune icône emoji : le jeu d'icônes est vectoriel
(`src/components/Icon.js`, react-native-svg).

## Installation

```bash
cd mobile
npm install
npx expo start
```

L'URL de l'API se règle dans `app.json` → `expo.extra.apiUrl`.
Sur émulateur Android, `localhost` du PC est `10.0.2.2`.

```json
"extra": { "apiUrl": "http://10.0.2.2:8000/api" }
```

## Structure

| Fichier | Rôle |
|---|---|
| `App.js` | navigation : 5 onglets + pile (match, effectif, joueur, médias, compétitions, à propos), en-tête commun |
| `src/theme.js` | charte issue du logo : bordeaux, orange, crème ; Anton (titres) + Manrope (texte) |
| `src/api.js` | client REST, cache mémoire 60 s, repli hors-ligne AsyncStorage, `usePolling` (15 s) pour le live |
| `src/components/Icon.js` | icônes SVG maison |
| `src/components/Ui.js` | Card, SectionTitle, Chip, Segmented, LiveDot, StatBar, MetricRow, Loader, EmptyState |
| `src/components/Crest.js` | écusson d'équipe (logo ou abréviation sur couleur du club) |

## Écrans

1. `HomeScreen` — match en direct ou prochain match, raccourcis classement / buteur, actualités
2. `FixturesScreen` — calendrier et résultats, filtres par compétition
3. `MatchScreen` — score live, fil des événements, compos, statistiques (rafraîchi toutes les 15 s)
4. `StandingsScreen` — classement, zones de qualification et de barrages
5. `TeamsScreen` — les 10 équipes avec recherche
6. `SquadScreen` — effectif par poste + indicateurs de l'équipe
7. `PlayerScreen` — fiche joueur, statistiques, buts par journée
8. `StatsScreen` — buteurs, passeurs, discipline / attaque, défense, possession, affluence
9. `MediaScreen` — photos et vidéos
10. `CompetitionsScreen` — Championnat, Grand Prix Gabriel MBAÏROBÉ, Super Coupe
11. `AboutScreen` — mission du GFC et contacts

## À faire avant mise en production

- remplacer `assets/logo.png` par le logo haute définition et générer les icônes Android/iOS
- notifications push (expo-notifications + endpoint `POST /devices` déjà présent côté API)
- écran de recherche globale (équipes + joueurs)
