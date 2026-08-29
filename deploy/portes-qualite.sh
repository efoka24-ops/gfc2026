#!/usr/bin/env bash
#
# Portes qualite du projet GFC — a lancer avant chaque fusion.
#
#   bash deploy/portes-qualite.sh
#
# Deux controles automatisables issus de la constitution :
#   principe III — aucun emoji dans l'interface (SC-008)
#   principe V   — aucune requete SQL construite par concatenation (SC-009)
#
# Le reste des portes se verifie a la main : etats chargement / vide / erreur,
# aucune couleur codee en dur hors du theme.

set -uo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

ECHECS=0
echo "Portes qualite — Garoua Football Challenge"
echo

# ------------------------------------------------------------------ emojis
# Plages Unicode des emoji et pictogrammes. Les fichiers de documentation sont
# exclus : la regle porte sur l'interface, pas sur les notes de travail.
echo "[1/2] Aucun emoji dans l'interface"
CIBLES=""
[ -d mobile/src ]      && CIBLES="$CIBLES mobile/src"
[ -f mobile/App.js ]   && CIBLES="$CIBLES mobile/App.js"
[ -d backend/public ]  && CIBLES="$CIBLES backend/public"
[ -d backend/src ]     && CIBLES="$CIBLES backend/src"

if [ -n "$CIBLES" ]; then
  # shellcheck disable=SC2086
  TROUVES=$(grep -rlP "[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}\x{1F000}-\x{1F02F}]" \
    --exclude="*.md" --exclude="*.png" --exclude="*.jpg" $CIBLES 2>/dev/null)
  if [ -n "$TROUVES" ]; then
    echo "  ECHEC — emoji detecte dans :"
    echo "$TROUVES" | sed 's/^/    /'
    echo "  Utilisez une icone du jeu SVG maison (principe III)."
    ECHECS=$((ECHECS + 1))
  else
    echo "  OK"
  fi
else
  echo "  ignore — aucun dossier d'interface sur cette branche"
fi
echo

# --------------------------------------------------------------- SQL en dur
# Detecte une variable PHP interpolee dans une chaine contenant un mot-cle SQL.
# Les requetes preparees passent leurs valeurs par ? et ne declenchent rien.
echo "[2/2] Aucune requete SQL construite par concatenation"
if [ -d backend ]; then
  TROUVES=$(grep -rnE --include="*.php" \
    "(SELECT|INSERT INTO|UPDATE |DELETE FROM)[^;\"']*(\\\$[a-zA-Z_]|\. *\\\$)" \
    backend/src backend/public 2>/dev/null | grep -v "^\s*//" )
  if [ -n "$TROUVES" ]; then
    echo "  ECHEC — variable interpolee dans une requete :"
    echo "$TROUVES" | sed 's/^/    /'
    echo "  Passez la valeur en parametre lie (principe V)."
    ECHECS=$((ECHECS + 1))
  else
    echo "  OK"
  fi
else
  echo "  ignore — pas de backend sur cette branche"
fi
echo

if [ "$ECHECS" -gt 0 ]; then
  echo "$ECHECS porte(s) en echec — fusion a suspendre."
  exit 1
fi

echo "Toutes les portes automatisees passent."
echo "Reste a verifier a la main : etats chargement / vide / erreur des ecrans"
echo "touches, et aucune couleur ni police codee en dur hors du theme."
