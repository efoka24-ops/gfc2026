<!--
Sync Impact Report
==================
Version change: (template, non ratifiée) → 1.0.0
Bump: MAJOR initial — première ratification, tous les principes définis.

Principes définis (aucun renommage, aucun retrait) :
  I.   Le direct est la promesse produit
  II.  Une seule source de vérité : les événements de match
  III. Identité visuelle stricte — icônes vectorielles, zéro emoji
  IV.  Français d'abord, terrain d'abord
  V.   Sécurité et intégrité des données de compétition

Sections ajoutées :
  - Contraintes techniques
  - Workflow de développement et portes qualité
  - Gouvernance

Templates vérifiés :
  ✅ .specify/templates/plan-template.md — la section « Constitution Check » est générique,
     elle lit ce fichier au moment du plan ; aucune modification requise.
  ✅ .specify/templates/spec-template.md — aucune section obligatoire ajoutée/retirée.
  ✅ .specify/templates/tasks-template.md — catégories de tâches compatibles.
  ✅ CLAUDE.md — guidance runtime, à enrichir au moment du /speckit-plan.

TODO différés : aucun.
-->

# Garoua Football Challenge Constitution

Le Garoua Football Challenge (GFC) est le championnat de vacances de Garoua, à sa
6e édition, réunissant 10 équipes réparties entre le Championnat, le Grand Prix
Gabriel MBAÏROBÉ et la Super Coupe. Sa mission — vulgariser les talents, mettre en
avant le professionnalisme, permettre aux jeunes footballeurs d'évoluer dans un
milieu professionnel — est la raison d'être de ce produit et arbitre chaque
décision technique.

## Core Principles

### I. Le direct est la promesse produit

Un match en cours DOIT être visible dans l'application dans les 20 secondes qui
suivent la saisie de l'événement par l'arbitre ou le secrétaire au back-office.
Le score, la minute, le fil des événements et le classement DOIVENT se
rafraîchir sans action manuelle de l'utilisateur (polling ≤ 15 s côté mobile).
Toute fonctionnalité qui dégrade cette latence DOIT être rejetée ou rendue
asynchrone.

*Rationale* : sans le direct, l'application n'est qu'un site d'archives ; le
public de Garoua suit le match depuis les gradins ou depuis la maison en temps
réel, et c'est ce qui fait exister la compétition au-delà du terrain.

### II. Une seule source de vérité : les événements de match

Les scores, classements, statistiques de joueurs et d'équipes NE DOIVENT JAMAIS
être stockés comme valeurs dénormalisées faisant autorité. Ils DOIVENT être
dérivés des lignes de `match_events` et des matchs terminés, via des vues SQL
(`v_standings`) ou des agrégats calculés à la lecture. Tout chiffre affiché DOIT
être traçable jusqu'à l'événement qui l'a produit. Un cache est autorisé s'il est
invalidé par l'écriture d'un événement, jamais s'il devient la référence.

*Rationale* : un classement désynchronisé du terrain détruit la crédibilité de la
compétition et fait perdre des heures de correction manuelle en pleine saison.

### III. Identité visuelle stricte — icônes vectorielles, zéro emoji

Aucun emoji NE DOIT apparaître dans l'interface mobile, le back-office, les
notifications ou les contenus générés. Toute icône DOIT être un vecteur SVG issu
du jeu maison (`src/components/Icon.js` côté mobile, sprite SVG côté back-office).
La charte — bordeaux, orange, crème issus du logo ; Anton pour les titres,
Manrope pour le texte — DOIT être consommée depuis `theme.js` (mobile) et les
variables CSS (web) ; aucune couleur ni police codée en dur dans un écran.

*Rationale* : le GFC se présente comme un cadre professionnel ; les emojis et les
valeurs en dur produisent une image amateur et rendent tout changement de charte
impossible à propager.

### IV. Français d'abord, terrain d'abord

Toute chaîne visible par l'utilisateur DOIT être en français, y compris les
messages d'erreur et les états vides. L'application DOIT rester utilisable dans
les conditions réelles de Garoua : réseau lent ou absent (dernier contenu servi
depuis le cache local), appareils Android d'entrée de gamme, saisie au bord du
terrain sur téléphone. Le back-office de saisie live DOIT rester utilisable au
pouce, en une main, sans clavier physique.

*Rationale* : les utilisateurs ne sont ni anglophones ni équipés de terminaux
haut de gamme ; une app qui suppose la 4G et un grand écran ne sera pas utilisée.

### V. Sécurité et intégrité des données de compétition

Toute requête SQL DOIT être préparée (`ATTR_EMULATE_PREPARES => false`) ; aucune
concaténation de variable dans une requête. Les mots de passe DOIVENT passer par
`password_hash`/`password_verify`, les jetons d'API DOIVENT expirer, chaque
formulaire du back-office DOIT porter un jeton CSRF, et toute sortie DOIT être
échappée. Les écritures DOIVENT être soumises aux rôles : admin (accès complet),
secrétaire (contenus), arbitre (saisie live uniquement). Les uploads DOIVENT être
contrôlés en extension et en taille.

*Rationale* : un score falsifié ou un compte compromis en pleine compétition est
un litige sportif, pas seulement un incident technique.

## Contraintes techniques

- **Mobile** : React Native via Expo, en français, navigation à 5 onglets plus
  une pile. Pas de dépendance UI lourde : les composants de `src/components/Ui.js`
  sont la bibliothèque du projet.
- **Backend et back-office** : PHP 8.1+ et MySQL 8, PDO uniquement, sans
  framework applicatif. L'API REST et le back-office partagent le même dossier
  `public/` et le même modèle de données.
- **Contrat d'API** : l'API REST sous `/api` est le seul point de contact entre
  mobile et serveur. Toute évolution de réponse cassante DOIT être versionnée ou
  additive ; le mobile NE DOIT JAMAIS lire la base directement.
- **Dépôt** : `github.com/efoka24-ops/gfc2026`. La branche `mobile` porte
  l'application React Native, la branche `web` porte l'API et le back-office ;
  `main` porte la documentation, la spécification et le schéma partagé.
- **Configuration** : aucun secret en dur ni commité. Base de données, URL d'API
  et identifiants passent par variables d'environnement (`GFC_*`) côté PHP et par
  `expo.extra` côté mobile.

## Workflow de développement et portes qualité

- Chaque fonctionnalité passe par le cycle Spec Kit : `specify` → `clarify` (si
  ambiguïté) → `plan` → `tasks` → `implement`. Aucune implémentation ne démarre
  sans spécification écrite.
- Le travail se fait sur des branches de fonctionnalité fusionnées dans `mobile`
  ou `web` selon le périmètre ; une fonctionnalité qui touche les deux DOIT
  livrer d'abord l'API, puis le mobile.
- Portes avant fusion : aucun emoji introduit ; aucune requête SQL non préparée ;
  aucune couleur ni chaîne codée en dur hors du thème ; les écrans touchés
  vérifiés en état chargement, vide et erreur ; le README du dossier concerné mis
  à jour si la commande de démarrage ou le contrat d'API change.
- Le jeu de données de démonstration (`sql/seed.sql`) NE DOIT PAS être confondu
  avec les données réelles ; les données de la 6e édition sont saisies via le
  back-office.

## Governance

Cette constitution prime sur toute autre pratique du projet. En cas de conflit
entre un choix technique et un principe, le principe l'emporte ou la constitution
est amendée explicitement — jamais contournée en silence.

Un amendement requiert : la modification de ce fichier, une justification écrite
dans le Sync Impact Report en tête de fichier, et la propagation aux templates et
documents dépendants. Le versionnage suit le semver : MAJOR pour un retrait ou une
redéfinition incompatible d'un principe, MINOR pour l'ajout ou l'extension
matérielle d'un principe ou d'une section, PATCH pour une clarification.

Toute revue de code DOIT vérifier la conformité aux portes qualité ci-dessus.
Toute complexité ajoutée DOIT être justifiée face au principe qu'elle met sous
tension. `CLAUDE.md` porte la guidance de développement au quotidien et DOIT
rester cohérent avec ce document.

**Version**: 1.0.0 | **Ratified**: 2026-08-29 | **Last Amended**: 2026-08-29
