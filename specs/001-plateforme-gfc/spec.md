# Feature Specification: Plateforme Garoua Football Challenge

**Feature Branch**: `001-plateforme-gfc`

**Created**: 2026-08-29

**Status**: Draft

**Input**: User description: "Application mobile du Garoua Football Challenge — compétition de vacances à sa 6e édition, 10 équipes, plusieurs compétitions (Grand Prix Gabriel MBAÏROBÉ, Super Coupe, Championnat). Objectif : vulgariser les talents, mettre en avant le professionnalisme, permettre aux jeunes footballeurs d'évoluer dans un milieu professionnel. Design en React Native, backend et back-office en PHP. Icônes vectorielles, pas d'emojis."

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Suivre un match en direct (Priority: P1)

Un supporter à Garoua ouvre l'application pendant qu'un match se joue. Il voit
immédiatement le score en cours, la minute de jeu et le fil des événements (buts,
cartons, remplacements) qui s'enrichit tout seul pendant qu'il regarde. En
parallèle, l'arbitre ou le secrétaire au bord du terrain saisit chaque événement
depuis le back-office sur son téléphone, et le score se recalcule sans qu'il ait
à le taper.

**Why this priority**: c'est la raison pour laquelle un supporter installe
l'application. Sans le direct, le produit n'apporte rien que le bouche-à-oreille
n'apporte déjà. C'est aussi la boucle complète saisie → diffusion, donc elle
valide à elle seule la chaîne back-office → API → mobile.

**Independent Test**: programmer un match, le passer en direct depuis le
back-office, saisir un but et un carton, et vérifier sur l'application mobile que
le score, la minute et les deux événements apparaissent sans action de
l'utilisateur.

**Acceptance Scenarios**:

1. **Given** un match au statut « en direct », **When** le supporter ouvre
   l'accueil, **Then** le match en direct est la première chose affichée, avec
   score, minute et indicateur visuel de direct.
2. **Given** un supporter sur la fiche d'un match en direct, **When** l'arbitre
   saisit un but au back-office, **Then** le score et le fil des événements se
   mettent à jour dans les 20 secondes sans intervention du supporter.
3. **Given** un but saisi pour l'équipe recevante, **When** le score est
   recalculé, **Then** le classement du championnat reflète le nouveau résultat
   dès que le match passe au statut « terminé ».
4. **Given** un événement saisi par erreur, **When** l'administrateur le
   supprime, **Then** le score et les statistiques du joueur concerné reviennent
   à leur valeur antérieure.
5. **Given** aucun match en cours, **When** le supporter ouvre l'accueil,
   **Then** le prochain match programmé est affiché avec sa date, son heure et
   son stade.

---

### User Story 2 — Consulter le classement et le calendrier (Priority: P1)

Un supporter veut savoir où en est son équipe : classement du championnat avec
points, différence de buts et zones de qualification, calendrier des prochaines
rencontres et résultats des journées passées, filtrables par compétition
(Championnat, Grand Prix Gabriel MBAÏROBÉ, Super Coupe).

**Why this priority**: c'est la consultation la plus fréquente entre deux matchs,
et elle donne à l'application une valeur permanente et non seulement les jours de
match. Elle est indépendante du direct : elle fonctionne sur les matchs terminés.

**Independent Test**: charger un jeu de matchs terminés et vérifier que le
classement calculé (points, différence de buts, ordre) correspond aux résultats,
et que les filtres par compétition renvoient les bonnes rencontres.

**Acceptance Scenarios**:

1. **Given** des matchs de championnat terminés, **When** le supporter ouvre le
   classement, **Then** les équipes sont ordonnées par points puis différence de
   buts puis buts marqués, avec journées jouées, gagnées, nulles, perdues.
2. **Given** le classement affiché, **When** le supporter le parcourt, **Then**
   les zones de qualification et de barrages sont distinguées visuellement.
3. **Given** plusieurs compétitions en cours, **When** le supporter filtre sur
   le Grand Prix Gabriel MBAÏROBÉ, **Then** seuls les matchs de cette compétition
   apparaissent dans le calendrier et les résultats.
4. **Given** un match terminé, **When** le supporter l'ouvre, **Then** il accède
   au score final, au fil des événements, aux compositions et aux statistiques
   de la rencontre.

---

### User Story 3 — Saisir et administrer la compétition (Priority: P1)

Le secrétaire de la compétition crée les compétitions de la saison, engage les 10
équipes, saisit les effectifs et les licences, programme le calendrier, puis
publie actualités, photos et vidéos. L'administrateur gère les comptes et les
rôles ; l'arbitre n'a accès qu'à la saisie live.

**Why this priority**: sans back-office, aucune donnée n'existe et les deux
premières histoires n'ont rien à afficher. C'est le préalable opérationnel de
toute la plateforme.

**Independent Test**: se connecter au back-office avec chacun des trois rôles et
vérifier que l'admin accède à tout, le secrétaire aux contenus, l'arbitre
uniquement à la saisie live ; créer une compétition, y engager deux équipes,
programmer un match et le retrouver dans l'API publique.

**Acceptance Scenarios**:

1. **Given** un utilisateur au rôle arbitre, **When** il ouvre le back-office,
   **Then** seule la saisie live lui est accessible et toute autre page est
   refusée.
2. **Given** un formulaire du back-office, **When** il est soumis sans jeton CSRF
   valide, **Then** la soumission est rejetée.
3. **Given** un secrétaire sur la page Actualités, **When** il enregistre un
   article en brouillon, **Then** l'article n'apparaît pas dans l'API publique
   tant qu'il n'est pas publié.
4. **Given** un fichier de plus de 25 Mo ou d'extension non autorisée, **When**
   il est envoyé dans la médiathèque, **Then** l'envoi est refusé avec un message
   explicite en français.
5. **Given** un match en cours de saisie live, **When** l'opérateur ajoute un
   événement, **Then** l'événement est horodaté à la minute saisie et attribué à
   une équipe et à un joueur de son effectif.

---

### User Story 4 — Découvrir les équipes et les joueurs (Priority: P2)

Un supporter, un recruteur ou un journaliste parcourt les 10 équipes de l'édition,
ouvre un effectif rangé par poste, puis la fiche d'un joueur avec ses
statistiques (buts, passes décisives, cartons) et sa progression au fil des
journées.

**Why this priority**: c'est la mission « vulgariser les talents » rendue
concrète, mais elle suppose que les effectifs et les événements existent déjà.

**Independent Test**: ouvrir une équipe, vérifier que l'effectif est complet et
groupé par poste, ouvrir un joueur et vérifier que chaque statistique correspond
aux événements saisis pour lui.

**Acceptance Scenarios**:

1. **Given** la liste des équipes, **When** le supporter saisit une recherche,
   **Then** la liste se filtre sur le nom de l'équipe.
2. **Given** la fiche d'une équipe, **When** le supporter l'ouvre, **Then** il
   voit l'écusson, la position au classement, les indicateurs de l'équipe et
   l'effectif groupé par poste.
3. **Given** la fiche d'un joueur, **When** le supporter l'ouvre, **Then** ses
   buts, passes décisives et cartons correspondent exactement aux événements
   enregistrés le concernant.

---

### User Story 5 — Consulter les classements statistiques (Priority: P2)

Le supporter consulte les meilleurs buteurs, les meilleurs passeurs et la
discipline, ainsi que les statistiques d'équipes : attaque, défense, possession,
affluence.

**Why this priority**: elle nourrit la mise en avant des talents et la
conversation autour de la compétition, sans être indispensable au premier match
diffusé.

**Independent Test**: comparer le classement des buteurs affiché avec le décompte
des événements « but » de la base.

**Acceptance Scenarios**:

1. **Given** des matchs terminés, **When** le supporter ouvre les buteurs,
   **Then** les joueurs sont ordonnés par nombre de buts et rattachés à leur
   équipe.
2. **Given** l'onglet équipes des statistiques, **When** le supporter l'ouvre,
   **Then** attaque, défense, possession et affluence sont affichées par équipe.

---

### User Story 6 — Suivre l'actualité et les médias (Priority: P3)

Le supporter lit les actualités publiées par l'organisation, regarde les photos
des rencontres et les vidéos de résumé, et consulte la page de présentation du
GFC avec sa mission et ses contacts.

**Why this priority**: elle donne vie à la marque de la compétition entre les
matchs, mais l'application reste utile sans elle.

**Independent Test**: publier une actualité et un média depuis le back-office et
les retrouver dans l'application.

**Acceptance Scenarios**:

1. **Given** une actualité publiée, **When** le supporter ouvre l'accueil,
   **Then** elle apparaît dans le fil d'actualités, la plus récente en premier.
2. **Given** la médiathèque, **When** le supporter bascule entre photos et
   vidéos, **Then** seuls les médias du type choisi sont affichés.

---

### User Story 7 — Être alerté des moments clés (Priority: P3)

Le supporter reçoit une notification au coup d'envoi d'un match, à chaque but et
au coup de sifflet final, sans avoir l'application ouverte.

**Why this priority**: c'est ce qui ramène l'utilisateur dans l'application, mais
cela dépend entièrement du direct (P1) qui doit être fiable avant d'être
notifié.

**Independent Test**: enregistrer un appareil, saisir un but au back-office et
vérifier la réception de la notification sur l'appareil.

**Acceptance Scenarios**:

1. **Given** une application installée, **When** elle démarre pour la première
   fois, **Then** l'appareil est enregistré auprès du serveur pour les
   notifications.
2. **Given** un appareil enregistré, **When** un but est saisi sur un match en
   direct, **Then** l'appareil reçoit une notification portant le nouveau score.

---

### Edge Cases

- **Réseau absent ou lent** : l'application affiche le dernier contenu connu
  depuis son cache local, avec une indication que les données ne sont pas à jour,
  plutôt qu'un écran vide ou une erreur technique.
- **Match reporté ou annulé** : le statut doit être visible dans le calendrier et
  la rencontre ne doit compter dans aucun classement.
- **Match à élimination directe se terminant sur un nul** : le résultat doit
  pouvoir se conclure aux tirs au but sans que ce score fausse la différence de
  buts.
- **Égalité parfaite au classement** : l'ordre doit être déterministe et
  documenté (points, puis différence de buts, puis buts marqués).
- **Même équipe vainqueur du championnat et du Grand Prix** : la Super Coupe
  perd son opposition. Le système doit permettre de désigner l'adversaire
  autrement (finaliste du Grand Prix ou deuxième du championnat) plutôt que de
  programmer une rencontre d'une équipe contre elle-même.
- **Joueur transféré entre deux équipes en cours d'édition** : ses statistiques
  doivent rester rattachées à l'équipe pour laquelle chaque événement a été
  marqué.
- **Deux opérateurs saisissant le même match simultanément** : aucun événement ne
  doit être perdu ni compté deux fois.
- **Événement saisi puis corrigé** : score, classement et statistiques doivent
  revenir à un état cohérent.
- **Compétition sans match joué** : les écrans de classement et de statistiques
  affichent un état vide explicite en français, pas une liste vide muette.
- **Fuseau horaire et heure locale** : les horaires de match s'affichent à l'heure
  de Garoua quelle que soit la configuration de l'appareil.

## Requirements *(mandatory)*

### Functional Requirements

**Compétitions et calendrier**

- **FR-001**: Le système DOIT gérer plusieurs compétitions par saison, au minimum
  le Championnat, le Grand Prix Gabriel MBAÏROBÉ et la Super Coupe, chacune avec
  son propre format.
- **FR-002**: Le système DOIT permettre d'engager des équipes dans une
  compétition, et une équipe DOIT pouvoir participer à plusieurs compétitions de
  la même saison.
- **FR-003**: Le système DOIT permettre de programmer un match avec date, heure,
  stade, arbitre et compétition de rattachement.
- **FR-004**: Le système DOIT distinguer les statuts de match : programmé, en
  direct, terminé, reporté, annulé, et n'inclure dans les classements que les
  matchs terminés.
- **FR-005**: Le Grand Prix Gabriel MBAÏROBÉ DOIT se dérouler à élimination
  directe à partir des quarts de finale — quarts, demi-finales, finale, soit
  8 équipes qualifiées et 7 matchs. Un tour ne peut pas se terminer sur un nul :
  le système DOIT permettre d'enregistrer un résultat aux tirs au but, distinct
  du score, qui désigne le qualifié sans entrer dans la différence de buts.
- **FR-006**: Le championnat DOIT se jouer en aller simple entre les 10 équipes,
  soit 9 journées et 45 matchs, à 3 points par victoire et 1 par match nul. Il
  ne comporte pas de barrages.
- **FR-034**: La Super Coupe DOIT opposer, en une rencontre unique, le vainqueur
  de la finale du Grand Prix Gabriel MBAÏROBÉ au vainqueur du championnat.
- **FR-035**: Le critère de qualification pour les quarts de finale du Grand Prix
  [NEEDS CLARIFICATION: les 8 premiers du championnat, ou un tirage au sort, ou
  un autre critère ?].

**Direct et événements**

- **FR-007**: Le système DOIT permettre à un opérateur autorisé d'enregistrer les
  événements d'un match : but, penalty, but contre son camp, carton jaune, carton
  rouge, remplacement, avec minute, équipe, joueur et passeur éventuel.
- **FR-008**: Le système DOIT recalculer le score d'un match à partir de ses
  événements dès qu'un événement est ajouté, modifié ou supprimé.
- **FR-009**: Les utilisateurs DOIVENT voir le score et le fil d'événements d'un
  match en direct se mettre à jour dans les 20 secondes suivant la saisie, sans
  action manuelle.
- **FR-010**: Le système DOIT permettre de mettre à jour en cours de match le
  statut, la minute, l'affluence, l'arbitre et le stade.

**Classements et statistiques**

- **FR-011**: Le système DOIT calculer le classement d'une compétition à partir
  des seuls matchs terminés, sans stocker de position faisant autorité.
- **FR-012**: Le classement DOIT présenter par équipe : matchs joués, gagnés,
  nuls, perdus, buts pour, buts contre, différence de buts et points, ordonnés
  par points puis différence de buts puis buts marqués.
- **FR-013**: Le classement DOIT signaler les zones de qualification et de
  barrages.
- **FR-014**: Le système DOIT produire les classements de buteurs, de passeurs et
  de discipline, dérivés des événements de match.
- **FR-015**: Le système DOIT produire des statistiques d'équipe : attaque,
  défense, possession et affluence.
- **FR-016**: Chaque statistique affichée DOIT être traçable jusqu'aux événements
  qui la produisent.

**Équipes et joueurs**

- **FR-017**: Le système DOIT gérer les fiches d'équipe (nom, abréviation,
  couleurs, écusson) et leurs effectifs.
- **FR-018**: Le système DOIT gérer les fiches de joueur avec poste, numéro et
  référence de licence.
- **FR-019**: Les utilisateurs DOIVENT pouvoir rechercher une équipe par son nom
  et consulter son effectif groupé par poste.
- **FR-020**: Le système DOIT afficher pour chaque joueur ses statistiques
  cumulées de l'édition et sa progression par journée.

**Contenus éditoriaux**

- **FR-021**: Le système DOIT permettre de rédiger une actualité, de la conserver
  en brouillon et de la publier ; seules les actualités publiées sont exposées
  publiquement.
- **FR-022**: Le système DOIT permettre d'ajouter des photos et des vidéos par
  envoi de fichier ou par URL externe, en contrôlant l'extension et la taille des
  fichiers envoyés.
- **FR-023**: Le système DOIT exposer une page de présentation du GFC portant sa
  mission et ses contacts.

**Comptes, rôles et sécurité**

- **FR-024**: Le système DOIT authentifier les opérateurs du back-office par
  identifiant et mot de passe, et l'application mobile ne DOIT exiger aucun
  compte pour la consultation.
- **FR-025**: Le système DOIT appliquer trois rôles : administrateur (accès
  complet), secrétaire (contenus et saisie), arbitre (saisie live uniquement).
- **FR-026**: Toute écriture par l'API DOIT exiger un jeton d'authentification à
  durée de vie limitée.
- **FR-027**: Tout formulaire du back-office DOIT être protégé contre la
  falsification de requête intersite.
- **FR-028**: Les mots de passe DOIVENT être stockés sous forme de condensats non
  réversibles.

**Expérience et présentation**

- **FR-029**: Toute chaîne visible par l'utilisateur DOIT être en français.
- **FR-030**: L'interface NE DOIT contenir aucun emoji ; toute icône DOIT être
  une icône vectorielle du jeu maison.
- **FR-031**: L'application mobile DOIT afficher le dernier contenu connu lorsque
  le réseau est indisponible, en signalant que les données ne sont pas à jour.
- **FR-032**: Chaque écran DOIT présenter un état de chargement, un état vide et
  un état d'erreur explicites.
- **FR-033**: Le système DOIT permettre l'enregistrement d'un appareil en vue des
  notifications de coup d'envoi, de but et de fin de match.

### Key Entities

- **Saison / Édition** : l'édition courante (la 6e) qui regroupe compétitions,
  engagements et matchs.
- **Compétition** : Championnat, Grand Prix Gabriel MBAÏROBÉ ou Super Coupe ;
  porte un format et un ensemble d'équipes engagées.
- **Équipe** : club participant ; nom, abréviation, couleurs, écusson, effectif.
- **Joueur** : membre d'un effectif ; poste, numéro, licence, rattaché à une
  équipe.
- **Match** : rencontre entre deux équipes dans une compétition ; date, stade,
  arbitre, statut, minute, affluence ; son score est dérivé de ses événements.
- **Événement de match** : fait de jeu horodaté à la minute — but, penalty,
  carton, remplacement — rattaché à un match, une équipe et un joueur. C'est la
  source de vérité de tous les chiffres du produit.
- **Composition** : joueurs alignés, titulaires et remplaçants, pour un match.
- **Classement** : vue dérivée des matchs terminés d'une compétition, jamais
  stockée comme valeur autoritaire.
- **Actualité** : article éditorial avec statut brouillon ou publié.
- **Média** : photo ou vidéo, envoyée ou référencée par URL.
- **Utilisateur** : opérateur du back-office porteur d'un rôle.
- **Appareil** : terminal enregistré pour recevoir les notifications.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Un événement saisi au back-office est visible dans l'application
  mobile en moins de 20 secondes dans 95 % des cas.
- **SC-002**: Un supporter atteint le score d'un match en direct en au plus deux
  interactions depuis l'ouverture de l'application.
- **SC-003**: Le classement affiché correspond exactement au recalcul manuel à
  partir des matchs terminés, dans 100 % des vérifications.
- **SC-004**: Un opérateur saisit un but complet (équipe, joueur, minute) en
  moins de 15 secondes sur téléphone, en une main.
- **SC-005**: L'application affiche un contenu utile en moins de 3 secondes sur
  un appareil Android d'entrée de gamme en réseau 3G.
- **SC-006**: Sans réseau, l'application affiche le dernier contenu connu au lieu
  d'une erreur dans 100 % des écrans de consultation.
- **SC-007**: Les 10 équipes et leurs effectifs complets de la 6e édition sont
  saisissables et consultables avant le premier match diffusé.
- **SC-008**: Zéro emoji dans l'interface, vérifié automatiquement avant chaque
  fusion.
- **SC-009**: Aucune requête SQL construite par concaténation dans le code livré.

## Assumptions

- L'édition couverte est la 6e, avec 10 équipes ; le nombre d'équipes doit rester
  paramétrable pour les éditions suivantes.
- La consultation est entièrement publique : aucun compte utilisateur côté
  supporter, donc pas de favoris ni de personnalisation dans cette version.
- La saisie live est faite par un opérateur humain au bord du terrain sur un
  téléphone, via le navigateur ; il n'existe pas de flux de données automatique
  depuis un prestataire.
- Le back-office est le seul point d'entrée des données réelles ; le jeu de
  données d'exemple sert uniquement au développement.
- La plateforme sert une seule compétition régionale : le volume attendu se
  compte en dizaines de matchs et en milliers d'utilisateurs, pas en millions.
- L'application est publiée d'abord sur Android, le public cible étant très
  majoritairement Android.
- Les logos, photos, vidéos et coordonnées définitives sont fournis par
  l'organisation du GFC avant la mise en service.
- La billetterie, la boutique, les paris et les commentaires d'utilisateurs sont
  hors périmètre de cette version.
