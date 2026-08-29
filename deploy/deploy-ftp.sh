#!/usr/bin/env bash
#
# Deploiement de la partie web du GFC vers gfc.trugroup.cm
#
# Aucun identifiant dans ce fichier : ils sont lus dans l'environnement.
#   cp deploy/.env.deploy.example deploy/.env.deploy   # ignore par git
#   set -a && . deploy/.env.deploy && set +a
#   bash deploy/deploy-ftp.sh --dry-run
#   bash deploy/deploy-ftp.sh
#
# Voir deploy/DEPLOIEMENT.md pour la procedure complete.

set -euo pipefail

HOST="${GFC_FTP_HOST:-ftp-12.camoo.net}"
PORT="${GFC_FTP_PORT:-21}"
USER="${GFC_FTP_USER:-trugro9159_gfc}"
REMOTE="${GFC_FTP_DIR:-/}"
PASS="${GFC_FTP_PASS:-}"

DRY_RUN=""
[ "${1:-}" = "--dry-run" ] && DRY_RUN="--dry-run"

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOCAL="$ROOT/backend"

if [ -z "$PASS" ]; then
  echo "Erreur : GFC_FTP_PASS n'est pas defini." >&2
  echo "Renseignez-le dans deploy/.env.deploy puis chargez-le dans l'environnement." >&2
  exit 1
fi

if ! command -v lftp >/dev/null 2>&1; then
  echo "Erreur : lftp est introuvable." >&2
  echo "Installez-le (apt install lftp / brew install lftp), ou transferez a la main" >&2
  echo "avec FileZilla en suivant deploy/DEPLOIEMENT.md." >&2
  exit 1
fi

if [ ! -d "$LOCAL/public" ]; then
  echo "Erreur : $LOCAL/public est introuvable. Lancez ce script depuis la branche web." >&2
  exit 1
fi

echo "Source      : $LOCAL"
echo "Destination : ftp://$HOST:$PORT$REMOTE (utilisateur $USER)"
[ -n "$DRY_RUN" ] && echo "Mode        : simulation, aucun fichier ne sera transfere"
echo

# --exclude-glob protege ce qui vit sur le serveur et ne doit jamais etre
# ecrase par le depot :
#   config.local.php   identifiants de production
#   public/uploads/    medias envoyes depuis le back-office
#   sql/seed.sql       donnees de demonstration, interdites en production
lftp -u "$USER,$PASS" -p "$PORT" "$HOST" <<LFTP
set ftp:ssl-allow true
set ftp:ssl-force false
set ssl:verify-certificate no
set net:max-retries 3
set net:timeout 20
mirror --reverse --delete --verbose $DRY_RUN \
  --exclude-glob config.local.php \
  --exclude-glob seed.sql \
  --exclude-glob .DS_Store \
  --exclude-glob *.log \
  --exclude uploads/ \
  "$LOCAL/" "$REMOTE"
bye
LFTP

echo
echo "Transfert termine."
echo
echo "Verifications a faire maintenant :"
echo "  curl -sS https://gfc.trugroup.cm/api/competitions"
echo "  curl -sS -o /dev/null -w '%{http_code}\\n' https://gfc.trugroup.cm/src/Database.php   # attendu : 403 ou 404"
echo "  curl -sS -o /dev/null -w '%{http_code}\\n' https://gfc.trugroup.cm/config/config.local.php"
