#!/usr/bin/env bash
set -euo pipefail

mode=${DEPLOY_MODE:-preview}
[[ "$mode" == preview || "$mode" == deploy ]] || { echo 'Invalid deployment mode'; exit 1; }
: "${SSH_DIRECTORY:?Missing SSH_DIRECTORY}"
: "${GITHUB_SHA:?Missing GITHUB_SHA}"
[[ "$GITHUB_SHA" =~ ^[0-9a-f]{40}$ ]] || { echo 'Invalid commit SHA'; exit 1; }

destination=u262228329@185.166.188.94
remote_root=/home/u262228329/domains/liranyuzbegi.com/public_html
ssh_options=(-F /dev/null -p 65002 -i "$SSH_DIRECTORY/key"
  -o BatchMode=yes -o IdentitiesOnly=yes -o StrictHostKeyChecking=yes
  -o "UserKnownHostsFile=$SSH_DIRECTORY/known_hosts" -o ConnectTimeout=15)

archive=dist/liranyuzbegi-hostinger.tar.gz
[[ -s "$archive" ]] || { echo "Missing package: $archive"; exit 1; }
payload=$(mktemp -d)
trap 'rm -rf -- "$payload"' EXIT
tar -xzf "$archive" -C "$payload"
for file in vendor/autoload.php public/assets/manifest.json public/assets/styles/portfolio.css; do
  [[ -s "$payload/$file" ]] || { echo "Missing packaged file: $file"; exit 1; }
done

ssh "${ssh_options[@]}" "$destination" 'bash -s' <<'REMOTE'
set -euo pipefail
cd /home/u262228329/domains/liranyuzbegi.com/public_html
test -f public/index.php
test -f .env
test -w var
command -v rsync >/dev/null
php -r 'exit(PHP_VERSION_ID >= 80300 ? 0 : 1);'
REMOTE

# Do not remove server files, environment configuration, uploads or old assets.
rsync_options=(-rlz --itemize-changes --delay-updates
  --exclude='.env*' --exclude='.htaccess' --exclude='/public/uploads/'
  --exclude='/public/router.php')
[[ "$mode" != preview ]] || rsync_options+=(--dry-run)
printf -v transport '%q ' ssh "${ssh_options[@]}"
rsync "${rsync_options[@]}" -e "$transport" "$payload/" "$destination:$remote_root/"

if [[ "$mode" == preview ]]; then
  echo 'Preview complete. No production files were changed.'
  exit 0
fi

ssh "${ssh_options[@]}" "$destination" 'bash -s' <<'REMOTE'
set -euo pipefail
cd /home/u262228329/domains/liranyuzbegi.com/public_html
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear --env=prod --no-debug
REMOTE

for path in / /about /research-and-artistic-practice /contact /assets/styles/portfolio.css; do
  curl --fail --silent --show-error --location --retry 2 --max-time 30 \
    --output /dev/null "https://liranyuzbegi.com$path"
done
echo "Deployed $GITHUB_SHA to https://liranyuzbegi.com"
