# Recherche et décisions techniques — Plateforme GFC

**Feature**: 001-plateforme-gfc | **Date**: 2026-08-29

Chaque décision est présentée avec sa justification et les alternatives écartées.

## D1 — Direct par polling HTTP plutôt que WebSocket

**Décision** : l'application mobile interroge l'API toutes les 15 secondes sur les
écrans Accueil et Match (`usePolling`), et uniquement quand l'écran est au
premier plan. Aucune connexion persistante.

**Justification** : la contrainte de la constitution est de 20 secondes, pas de
temps réel à la seconde. Un intervalle de 15 s laisse 5 s de marge réseau. Un
match en direct concerne quelques milliers d'appareils au maximum, soit un ordre
de grandeur de quelques centaines de requêtes par seconde en pointe sur des
réponses courtes et cachables — parfaitement tenable en PHP.

**Alternatives écartées** :
- *WebSocket / Server-Sent Events* : impose un processus serveur à état, absent
  d'un hébergement PHP mutualisé, et ajoute une reconnexion à gérer sur un réseau
  mobile instable. Le gain de latence (15 s → 1 s) n'est pas demandé.
- *Push comme seul canal de direct* : les notifications ne sont pas garanties ni
  ordonnées ; elles complètent le direct (phase 7) mais ne peuvent pas le porter.

## D2 — Score dérivé des événements, mis en cache dans `matches`

**Décision** : `matches.home_score` et `matches.away_score` existent mais sont un
cache. Ils sont recalculés par `Score::recompute($matchId)` à chaque ajout,
modification ou suppression d'un événement, dans la même transaction. Aucun
formulaire ne les saisit directement.

**Justification** : le principe II interdit un score autoritaire dénormalisé, mais
recalculer le score de tous les matchs à chaque lecture du calendrier coûterait
une agrégation par ligne. Le cache écrit dans la même transaction que l'événement
ne peut pas diverger : il n'existe pas de chemin d'écriture qui touche
`match_events` sans passer par `Score`.

**Alternatives écartées** :
- *Score calculé à la lecture, sans colonne* : agrégation sur `match_events` pour
  chaque match de chaque liste, coûteuse sur le calendrier et le classement.
- *Score saisi à la main par l'opérateur* : deux sources de vérité, exactement ce
  que le principe II proscrit.

## D3 — Classement en vue SQL `v_standings`

**Décision** : le classement est une vue SQL qui déplie chaque match terminé en
deux lignes (recevant, visiteur) et agrège points, différence de buts et bilan.
Aucune table de classement.

**Justification** : c'est l'application directe du principe II. La vue est
recalculée par MySQL à la lecture, sur quelques dizaines de matchs — coût
négligeable. Aucune tâche de recalcul à planifier, aucune désynchronisation
possible après une correction d'événement.

**Alternatives écartées** :
- *Table `standings` mise à jour par déclencheur* : ajoute un chemin d'écriture
  invisible, difficile à corriger après un événement supprimé.
- *Classement calculé côté PHP* : duplique en PHP une logique que SQL exprime
  mieux, et empêche le back-office et l'API de partager la même définition.

## D4 — Ordre de classement déterministe

**Décision** : points décroissants, puis différence de buts, puis buts marqués,
puis nom de l'équipe. Le dernier critère garantit un ordre stable même à égalité
parfaite.

**Justification** : la spécification exige un ordre déterministe et documenté
(cas limite « égalité parfaite »). Sans dernier critère, MySQL peut renvoyer deux
ordres différents pour la même donnée, ce qui se lit comme un bug.

**Alternative écartée** : *confrontation directe comme second critère* — plus
juste sportivement, mais elle demande une règle de tournoi validée par
l'organisation, qui fait partie des points ouverts FR-005/FR-006.

## D5 — Consultation publique, sans compte utilisateur

**Décision** : toutes les routes de lecture de l'API sont publiques. Seules les
routes d'écriture exigent un jeton. L'application mobile n'a aucun écran de
connexion.

**Justification** : le produit vise l'audience la plus large possible avec la
friction la plus faible ; un mur d'inscription ferait perdre l'essentiel du
public un jour de match. Les fonctions qui demanderaient un compte (favoris,
personnalisation) sont hors périmètre de cette version.

**Alternative écartée** : *compte supporter avec équipe favorite* — reporté ;
`device_tokens.favourite_team_id` permet déjà de cibler les notifications sans
compte.

## D6 — Cache mémoire 60 s et repli AsyncStorage côté mobile

**Décision** : `api.js` garde une réponse 60 s en mémoire et écrit chaque réponse
réussie dans AsyncStorage. En cas d'échec réseau, le dernier contenu connu est
servi avec un indicateur « données non à jour ». Les routes de direct ignorent le
cache mémoire.

**Justification** : le principe IV impose l'usage en réseau dégradé. Le cache
mémoire évite de retélécharger la même liste en navigant entre écrans ; le repli
disque est ce qui distingue un écran utile d'un écran d'erreur hors réseau.

**Alternative écartée** : *bibliothèque de cache et de synchronisation
(react-query, redux-persist)* — dépendance lourde pour un besoin que 80 lignes
couvrent, contraire à la contrainte « pas de dépendance UI ou d'état lourde ».

## D7 — PHP sans framework

**Décision** : un routeur unique dans `public/index.php`, une couche `src/` de
cinq classes, PDO direct. Aucun framework, aucun Composer applicatif.

**Justification** : contrainte explicite de la constitution. Le périmètre — une
vingtaine de routes de lecture et quatre d'écriture — ne justifie pas un
framework, et l'absence de dépendances rend le déploiement possible sur n'importe
quel hébergement PHP mutualisé de Garoua, sans accès SSH.

**Alternatives écartées** : *Laravel / Slim* — dépendances, build et exigences
d'hébergement disproportionnés pour ce périmètre.

## D8 — Icônes SVG maison

**Décision** : un composant `Icon.js` unique côté mobile, exposant les icônes du
produit par nom, et un sprite SVG côté back-office. Aucune bibliothèque d'icônes.

**Justification** : le principe III interdit les emojis et impose un jeu
vectoriel maison ; un jeu maison garantit aussi la cohérence de trait avec la
charte issue du logo.

**Alternative écartée** : *`@expo/vector-icons`* — dépendance de plusieurs
mégaoctets pour une quinzaine d'icônes, et des familles graphiques hétérogènes.

## D9 — Trois branches de longue durée

**Décision** : `main` porte documentation, spécification et schéma partagé ;
`web` porte l'API et le back-office ; `mobile` porte l'application React Native.
Les branches de fonctionnalité partent de `web` ou `mobile`.

**Justification** : demande explicite. Elle correspond aussi à deux cycles de
livraison réellement distincts — un déploiement serveur d'un côté, une
publication de binaire de l'autre.

**Conséquence à surveiller** : le contrat d'API est le seul lien entre les deux
branches. Il vit sur `main` (`specs/001-plateforme-gfc/contracts/api.md`) et toute
évolution cassante doit y être décrite avant d'être implémentée d'un côté ou de
l'autre.

## D10 — Notifications par Expo Push

**Décision** : enregistrement de l'appareil via `POST /api/devices`
(`device_tokens`), envoi depuis le serveur PHP vers le service Expo Push lors du
coup d'envoi, d'un but et du coup de sifflet final.

**Justification** : Expo Push évite d'intégrer directement Firebase et APNs et
fonctionne avec un simple appel HTTP sortant depuis PHP, compatible avec un
hébergement mutualisé. La table `device_tokens` existe déjà.

**Alternative écartée** : *Firebase Cloud Messaging en direct* — impose une
configuration native et un compte de service, pour un gain nul à ce volume.

## Points ouverts — hypothèses de travail

Ces hypothèses tiennent jusqu'à arbitrage de l'organisation du GFC ; elles sont
isolées dans la configuration de la compétition et non dans le code.

- **FR-006, format du championnat** : aller simple entre les 10 équipes, soit
  9 journées et 45 matchs, 3 points par victoire et 1 par nul.
- **FR-005, Grand Prix Gabriel MBAÏROBÉ** : matchs secs à élimination directe
  (quarts, demi-finales, finale) ; en cas d'égalité au temps réglementaire, tirs
  au but, dont le résultat est enregistré séparément du score afin de ne pas
  fausser la différence de buts.
- **Super Coupe** : rencontre unique entre le vainqueur du championnat et celui du
  Grand Prix.

Le schéma couvre déjà les trois formats via `competitions.type`
(`league` / `cup` / `supercup`), `matches.matchday`, `matches.round_label` et
`competition_team.group_name` : un arbitrage différent modifie les données
saisies, pas la structure. **Une colonne reste à ajouter** pour les tirs au but
(voir `data-model.md`, écart E1).
