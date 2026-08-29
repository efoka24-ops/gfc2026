# Démarrage et recette — Plateforme GFC

**Feature**: 001-plateforme-gfc | **Date**: 2026-08-29

## Prérequis

- PHP 8.1 ou supérieur, avec l'extension `pdo_mysql`
- MySQL 8
- Node.js 18 ou supérieur et npm
- Un téléphone Android avec Expo Go, ou un émulateur Android

## 1. Base de données

```bash
mysql -u root -p < backend/sql/schema.sql
mysql -u root -p < backend/sql/seed.sql   # démonstration uniquement, jamais en production
```

## 2. API et back-office (branche `web`)

```bash
export GFC_DB_HOST=127.0.0.1
export GFC_DB_NAME=gfc
export GFC_DB_USER=root
export GFC_DB_PASS=votre-mot-de-passe
export GFC_BASE_URL=http://localhost:8000

cd backend && php -S localhost:8000 -t public
```

- API : <http://localhost:8000/api/competitions>
- Back-office : <http://localhost:8000/admin/login.php>

Compte de démonstration : `admin@gfc.cm` / `gfc2026`. **Régénérez le condensat
avant toute mise en service** :

```bash
php -r "echo password_hash('votre-mot-de-passe', PASSWORD_DEFAULT), PHP_EOL;"
```

## 3. Application mobile (branche `mobile`)

```bash
cd mobile && npm install && npx expo start
```

L'URL de l'API se règle dans `app.json` → `expo.extra.apiUrl`. Sur émulateur
Android, `localhost` du poste est `10.0.2.2` :

```json
"extra": { "apiUrl": "http://10.0.2.2:8000/api" }
```

## Recette — scénario de bout en bout

Ce scénario valide la chaîne complète et les trois histoires de priorité P1.

### A. Direct (US1)

1. Back-office → **Matchs**, programmer une rencontre entre deux équipes du
   championnat, à l'heure courante.
2. Back-office → **Saisie live**, sélectionner ce match, passer son statut à
   « en direct », régler la minute à 10.
3. Ouvrir l'application mobile sur l'accueil. → *Le match doit apparaître en tête,
   avec l'indicateur de direct.*
4. Back-office, saisir un but pour l'équipe recevante avec un buteur et un
   passeur. → *Sur le mobile, sans y toucher, le score passe à 1-0 et le but
   apparaît dans le fil en moins de 20 secondes.*
5. Saisir un carton jaune pour l'équipe visiteuse. → *L'événement apparaît, le
   score ne bouge pas.*
6. Supprimer le but saisi. → *Le score revient à 0-0 et les statistiques du
   buteur reviennent à leur valeur antérieure.*

### B. Classement et calendrier (US2)

7. Saisir à nouveau le but, puis passer le match au statut « terminé ».
   → *L'équipe recevante gagne 3 points au classement, la différence de buts est
   à +1.*
8. Recalculer le classement à la main depuis les matchs terminés. → *Il doit
   correspondre exactement à celui affiché (SC-003).*
9. Sur mobile, filtrer le calendrier sur le Grand Prix Gabriel MBAÏROBÉ. → *Seuls
   les matchs de cette compétition apparaissent.*

### C. Rôles et sécurité (US3)

10. Se connecter au back-office avec un compte `arbitre`. → *Seule la saisie live
    est accessible ; toute autre page est refusée.*
11. Soumettre un formulaire sans jeton CSRF valide. → *La soumission est rejetée.*
12. Enregistrer une actualité en brouillon. → *Elle n'apparaît pas dans
    `GET /api/news`.*
13. Envoyer un fichier de plus de 25 Mo dans la médiathèque. → *Refus avec un
    message explicite en français.*

### D. Conditions de terrain (principe IV)

14. Couper le réseau du téléphone et naviguer entre les écrans. → *Le dernier
    contenu connu s'affiche, avec l'indication que les données ne sont pas à
    jour ; aucun écran d'erreur technique.*
15. Ouvrir une compétition sans aucun match joué. → *Un état vide explicite en
    français s'affiche, pas une liste muette.*

## Portes qualité avant fusion

```bash
# Aucun emoji dans l'interface (SC-008)
grep -rPn "[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]" mobile/src backend/public backend/src && echo "ÉCHEC : emoji détecté"

# Aucune requête SQL construite par concaténation (SC-009)
grep -rn --include=*.php -E "(SELECT|INSERT|UPDATE|DELETE).*(\\\$|\. *\\\$)" backend/src backend/public
```

À vérifier en plus, à la main, sur chaque écran touché : état de chargement, état
vide, état d'erreur ; aucune couleur ni police codée en dur hors de `theme.js`
(mobile) ou des variables CSS (back-office).

## Production

L'installation de production est décrite dans `deploy/DEPLOIEMENT.md` sur la
branche `web` : hébergement `gfc.trugroup.cm` (`/home/trugro9159/gfc`, Camoo),
disposition des dossiers imposée par le confinement du compte FTP, script de
transfert et vérifications d'après-déploiement.

Deux points restent à régler avant l'ouverture au public :

- émettre un certificat SSL couvrant `gfc.trugroup.cm` (AutoSSL ou Let's Encrypt
  dans cPanel) — celui présenté aujourd'hui ne couvre pas le sous-domaine, ce qui
  empêche l'application mobile de joindre l'API ;
- régénérer le mot de passe FTP et celui du compte administrateur du back-office.
