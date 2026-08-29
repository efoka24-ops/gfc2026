# Garoua Football Challenge — 6e édition

Plateforme du Garoua Football Challenge : le championnat de vacances de Garoua,
10 équipes, trois compétitions — le Championnat, le Grand Prix Gabriel MBAÏROBÉ
et la Super Coupe. Sa mission : vulgariser les talents, mettre en avant le
professionnalisme, permettre aux jeunes footballeurs d'évoluer dans un milieu
professionnel.

## Organisation du dépôt

| Branche | Contenu |
|---|---|
| `main` | documentation, constitution, spécification et planning |
| `web` | API REST PHP 8 et back-office d'administration (`backend/`) |
| `mobile` | application React Native Expo en français (`mobile/`) |

Les branches de fonctionnalité partent de `web` ou de `mobile` selon leur
périmètre. Une fonctionnalité qui touche les deux livre d'abord l'API, puis le
mobile.

## Hébergement

La partie web est hébergée sur **https://gfc.trugroup.cm**
(`/home/trugro9159/gfc`, Camoo) : l'API sur `/api`, le back-office sur
`/admin/login.php`. C'est cette installation que consomme l'application mobile.
Procédure de déploiement, script de transfert et vérifications :
[`deploy/DEPLOIEMENT.md`](https://github.com/efoka24-ops/gfc2026/blob/web/deploy/DEPLOIEMENT.md)
sur la branche `web`.

Aucun identifiant — FTP, base de données, compte administrateur — n'est
versionné : ils vivent dans `deploy/.env.deploy` et
`backend/config/config.local.php`, tous deux ignorés par git.

## Documents de référence

- [Constitution du projet](.specify/memory/constitution.md) — les cinq principes
  non négociables et les portes qualité
- [Spécification fonctionnelle](specs/001-plateforme-gfc/spec.md) — histoires
  utilisateur, exigences, critères de succès
- [Plan d'implémentation](specs/001-plateforme-gfc/plan.md) — approche technique
  et phasage
- [Décisions techniques](specs/001-plateforme-gfc/research.md) — choix retenus et
  alternatives écartées
- [Modèle de données](specs/001-plateforme-gfc/data-model.md) — entités, règles
  de dérivation, invariants
- [Contrat d'API](specs/001-plateforme-gfc/contracts/api.md) — **le seul lien
  entre `web` et `mobile`**
- [Démarrage et recette](specs/001-plateforme-gfc/quickstart.md)
- [Tâches](specs/001-plateforme-gfc/tasks.md) — 87 tâches en 10 phases

## Le principe qui gouverne tout le reste

Rien n'est stocké de ce qui peut être dérivé. Le score d'un match est recalculé
depuis ses événements, le classement est une vue SQL sur les seuls matchs
terminés, les statistiques des joueurs sont des agrégats sur les événements
saisis. Tout chiffre affiché est traçable jusqu'à l'événement qui l'a produit —
c'est ce qui garantit qu'un classement ne peut pas se désynchroniser du terrain.

## Démarrage

```bash
# API et back-office
git checkout web
mysql -u root -p < backend/sql/schema.sql
cd backend && php -S localhost:8000 -t public

# Application mobile
git checkout mobile
cd mobile && npm install && npx expo start
```

Détail complet dans [quickstart.md](specs/001-plateforme-gfc/quickstart.md).

## Points ouverts

Deux formats de compétition restent à arbitrer avec l'organisation du GFC
(FR-005 et FR-006 de la spécification) : le nombre de journées du championnat et
le format exact du Grand Prix Gabriel MBAÏROBÉ. Les hypothèses de travail en
vigueur sont documentées dans
[research.md](specs/001-plateforme-gfc/research.md). Elles ne bloquent que la
phase 5 du planning.
