# Liran Yuzbegi — Portfolio

Portfolio bilingue (anglais/hébreu) de Liran Yuzbegi, artiste et chercheuse
interdisciplinaire travaillant autour du son, de la musique, du cinéma
expérimental et de l'art contemporain.

Le projet repose sur Symfony 7.4 LTS et fournit :

- un site public en anglais (`/en`) et en hébreu (`/he`) ;
- une mise en page RTL native pour l'hébreu ;
- un catalogue musical avec covers et fichiers MP3 ;
- une administration sécurisée pour gérer les chansons ;
- un formulaire de contact ;
- des métadonnées SEO bilingues, `hreflang`, JSON-LD, sitemap et robots.txt ;
- des pages d'erreur personnalisées et des en-têtes HTTP de sécurité.

## Prérequis

### Avec Docker (recommandé en local)

- Docker Desktop ;
- Docker Compose.

### Sans Docker

- PHP 8.3 recommandé (PHP 8.2 minimum) ;
- Composer 2 ;
- MySQL 8 ;
- les extensions PHP requises par Symfony et Doctrine.

## Installation locale avec Docker

```bash
git clone https://github.com/edenolam/liranyuzbegi.git
cd liranyuzbegi
docker compose up -d --build
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

Services disponibles :

- site : <http://localhost:8001>
- Mailpit : <http://localhost:8026>
- MySQL : `127.0.0.1:3307`

Les identifiants présents dans `compose.yaml` sont exclusivement destinés au
développement local.

## Créer un administrateur

```bash
docker compose exec app php bin/console app:create-user admin@example.com 'mot-de-passe-fort'
```

L'administration est ensuite accessible sur `/admin`. N'utilisez jamais les
identifiants de développement en production.

## Tests et contrôles

```bash
docker compose exec -e APP_ENV=test app php bin/phpunit
docker compose exec app php bin/console lint:twig templates
docker compose exec app php bin/console lint:yaml config translations
docker compose exec app php bin/console lint:container
docker compose exec app php bin/console doctrine:schema:validate
docker compose exec app php bin/console asset-map:compile --env=prod
composer audit --locked
```

État de référence au dernier contrôle : **13 tests et 64 assertions réussis**.

## Médias

Les fichiers envoyés depuis l'administration sont enregistrés dans :

- `public/uploads/covers/`
- `public/uploads/songs/`

Ces répertoires doivent être inscriptibles par PHP et sauvegardés séparément de
la base de données. Un déploiement ne doit pas supprimer leur contenu.

## Configuration

Les valeurs sensibles doivent être fournies par l'environnement de production
ou dans un fichier `.env.local` non versionné :

```dotenv
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=change-me
DATABASE_URL="mysql://user:password@host:3306/database?serverVersion=8.0.32&charset=utf8mb4"
MAILER_DSN="smtp://user:password@smtp.example.com:587"
```

Ne commitez jamais les véritables secrets, mots de passe ou accès SMTP.

## Déploiement sur Hostinger depuis GitHub

Le dépôt peut être relié à Hostinger depuis :

**hPanel → Sites web → Tableau de bord → Avancé → Git**

Sélectionnez le dépôt `edenolam/liranyuzbegi`, la branche `main` et vérifiez
attentivement le répertoire cible déjà utilisé par le site avant d'activer le
déploiement automatique.

Pour chaque version, les opérations de production attendues sont :

```bash
composer install --no-dev --classmap-authoritative --no-interaction
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear --env=prod
php bin/console asset-map:compile --env=prod
```

Points importants sur Hostinger :

- la racine web doit exposer le contenu de `public/`, pas les fichiers internes
  de Symfony ;
- `.env.local` et `public/uploads/` doivent être conservés entre les versions ;
- `var/`, `public/uploads/covers/` et `public/uploads/songs/` doivent être
  inscriptibles par PHP ;
- le déploiement Git met à jour le code, mais ne remplace pas une sauvegarde de
  la base et des médias ;
- testez l'administration et un upload après le premier déploiement.

La checklist détaillée se trouve dans
[`docs/PRODUCTION.md`](docs/PRODUCTION.md).

## Routes principales

| Fonction | Anglais | Hébreu |
|---|---|---|
| Accueil | `/en` | `/he` |
| À propos | `/en/about` | `/he/about` |
| Musique | `/en/songs` | `/he/songs` |
| Contact | `/en/contact` | `/he/contact` |

Routes techniques :

- `/admin`
- `/sitemap.xml`
- `/robots.txt`

## Licence

Projet propriétaire. Les textes, images et œuvres audio restent la propriété de
leurs ayants droit.
