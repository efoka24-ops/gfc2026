# Dashboard Web — GFC Championnat 2026

Interface d'administration React TypeScript pour le Garoua Football Challenge.

## Stack
- **React 18.3** + **TypeScript 5**
- **Vite 5.4** : Build tool
- **React Router 7** : Navigation
- **Axios** : HTTP client

## Installation

```powershell
cd web
npm install
npm run dev
# Ouvrir http://localhost:5173
```

## Configuration API

L'URL de l'API est dans [src/services/api.ts](src/services/api.ts#L3) :

```typescript
const API_BASE_URL = 'http://localhost:8000/api';
```

En production, remplacer par `https://gfc.camoo.hosting/api`.

## Pages

### 1. LoginPage (`/login`)
- Formulaire email/mot de passe
- Token stocké dans localStorage
- Redirection vers dashboard après login

### 2. DashboardPage (`/`)
- **Stats** : Nombre d'équipes, matchs live, journées, compétitions
- **Matchs live** : Liste matchs en cours (polling 15s)
- **Top 5** : Mini-classement Championnat
- **Compétitions** : 3 cartes (Championnat, GP Gabriel, Super Coupe)

### 3. MatchesPage (`/matches`)
- Table tous les matchs
- Filtres : Tous, Live, Programmés, Terminés
- Bouton "Gérer" → LiveMatchPage

### 4. LiveMatchPage (`/matches/:id`)
- **Score** : Affichage large avec statut badge
- **Contrôles** : Démarrer, Mi-temps, Reprendre, Terminer
- **Événements** : Liste avec dots colorés (vert=but, jaune=carton, rouge=exclusion)
- **Ajouter événement** : Form avec type, minute, équipe, joueur

### 5. StandingsPage (`/standings`)
- Sélecteur compétition (Championnat, GP Gabriel)
- Table complète : Rang, Équipe, J, V, N, D, BP, BC, DB, Pts
- **Bordure orange** : Top 8 (qualifiés pour GP Gabriel)

### 6. TeamsPage (`/teams`)
- Liste équipes (admin : boutons Create/Edit/Delete)

## Design System

Copié depuis la maquette mobile pour cohérence visuelle.

### Couleurs CSS
```css
--color-primary: #5A1424;   /* Bordeaux */
--color-secondary: #E8752A; /* Orange */
--color-bg: #FDF4E8;        /* Crème */
--color-text: #1A1A1A;      /* Texte principal */
```

### Typographie
- **Anton** : Titres et scores
- **Manrope** : Corps de texte

### Composants
- **Layout** : Sidebar + logo + navigation + logout
- **Badge** : Statuts matchs (scheduled, live, half_time, finished)
- **Button** : Primary, secondary, danger variants
- **Card** : Conteneurs avec border + shadow

## Authentification

Token JWT stocké dans `localStorage` :
```typescript
const token = localStorage.getItem('token');
```

Intercepteur Axios injecte automatiquement le Bearer token :
```typescript
config.headers.Authorization = `Bearer ${token}`;
```

Redirection vers `/login` sur 401.

## Build Production

```powershell
npm run build
# Dossier dist/ contient les fichiers statiques
```

Uploader `dist/` vers `/public_html/dashboard` sur Camoo FTP.

## TODO Avant Production

- [ ] Remplacer URL API hardcodée par variable d'environnement `.env`
- [ ] Ajouter page CRUD joueurs (Players)
- [ ] Implémenter WebSocket (Pusher) au lieu du polling
- [ ] Tester réactivité mobile (responsive design)
- [ ] Ajouter page statistiques (buteurs, cartons)
