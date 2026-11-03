# Déployer avec GitHub Actions

**PR → fusion dans `main` → vérifications → déploiement Hostinger.**

Créer ou publier une PR ne modifie pas le site. La fusion autorise la livraison.
Un push direct sur `main` la déclenche également : protéger `main` si les
changements doivent obligatoirement passer par une PR.

## Configuration unique : deux secrets du dépôt

Ouvrir le dépôt `edenolam/liranyuzbegi`, puis :

**Settings → Secrets and variables → Actions → Secrets → New repository secret**

Ajouter ces deux secrets directement dans le dépôt, pas dans Environments :

| Nom | Valeur |
| --- | --- |
| `HOSTINGER_SSH_KEY` | Contenu de la clé privée SSH autorisée pour Liran |
| `HOSTINGER_KNOWN_HOSTS` | Ligne(s) de clé d'hôte vérifiée(s) pour `[185.166.188.94]:65002` |

Aucun environnement GitHub `production` n'est utilisé. La variable
`HOSTINGER_DEPLOY_ENABLED` n'est plus consultée et peut rester sans effet.
Les secrets éventuellement créés dans un environnement ne sont pas lus : les
renseigner comme **Repository secrets**.

La clé dédiée déjà préparée sur cet ordinateur est
`~/.ssh/liranyuzbegi_hostinger`. Sa clé publique est autorisée sur Hostinger et
la connexion a été testée. Inutile de générer une nouvelle clé.

Pour copier la première valeur sans l'afficher :

```bash
xclip -selection clipboard < ~/.ssh/liranyuzbegi_hostinger
```

Pour copier la seconde :

```bash
xclip -selection clipboard < ~/.ssh/liranyuzbegi_hostinger_known_hosts
```

Coller chaque valeur dans le secret correspondant. Ne jamais commiter les clés
ni les partager dans une conversation. Enregistrer les deux secrets avant de
fusionner la PR ; s'ils manquent, la livraison échoue avant l'accès au serveur.

## Fonctionnement

- `Check project` vérifie automatiquement les PR et les pushes sur `main`.
- `Deploy to Hostinger` reconstruit et valide la production PHP 8.3 après fusion.
- Une archive temporaire contient le code, les dépendances et les assets
  compilés. GitHub la prépare et la transfère automatiquement : aucune
  manipulation d'archive n'est demandée à l'utilisateur.
- Le serveur sauvegarde ses fichiers hors de la racine web avant tout transfert,
  puis reçoit le code, reconstruit son cache et vérifie les pages publiques.
- Une seule livraison peut s'exécuter à la fois.

Un aperçu sans transfert reste disponible dans **Actions → Deploy to Hostinger
→ Run workflow → main → preview**. Les secrets sont nécessaires pour cet aperçu.

## Conservation du site

La cible est `/home/u262228329/domains/liranyuzbegi.com/public_html`, via
`u262228329@185.166.188.94:65002`, pour `https://liranyuzbegi.com`.

Le workflow conserve les fichiers `.env*`, les `.htaccess`, `var/` et
`public/uploads/`. Il n'exécute aucune migration ni initialisation de données.
La base de production n'est jamais remplacée par la base locale.

Les sauvegardes sont dans `/home/u262228329/liranyuzbegi-deploy-backups/`, avec
des permissions privées. Elles incluent configuration et médias, hors caches
et logs, mais pas la base de données. Un échec de sauvegarde bloque le transfert.

La mise à jour se fait en place, sans suppression distante ni retour arrière
automatique. Un échec peut demander une restauration manuelle. Les fichiers
supprimés ou renommés dans Git doivent être retirés explicitement sur le serveur.
L'intégration Git hPanel reste déconnectée pour éviter un deuxième déploiement.

## Vérifications locales sans accès distant

```bash
python3 -B -m unittest discover -s tests/Deployment -v
```
