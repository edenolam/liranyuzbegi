#!/usr/bin/env bash
set -euo pipefail

for file in vendor/autoload.php public/assets/manifest.json public/assets/styles/portfolio.css; do
  [[ -s "$file" ]] || { echo "Missing build file: $file"; exit 1; }
done

package=$(mktemp -d)
trap 'rm -rf -- "$package"' EXIT
rsync -r --exclude='.env*' --exclude='.htaccess' --exclude='/public/uploads/' \
  --exclude='/public/router.php' \
  assets bin config migrations public src templates translations vendor \
  composer.json composer.lock symfony.lock importmap.php "$package/"
mkdir -p dist
tar -czf dist/liranyuzbegi-hostinger.tar.gz -C "$package" .
