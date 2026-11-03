#!/usr/bin/env bash
set -euo pipefail

composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --classmap-authoritative
php bin/console lint:twig templates
php bin/console lint:yaml config translations
php bin/console lint:container
php bin/console importmap:install
php bin/console assets:install public
php bin/console asset-map:compile
# The portfolio template also references this stable filename.
cp assets/styles/portfolio.css public/assets/styles/portfolio.css
