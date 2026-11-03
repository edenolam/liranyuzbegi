# GitHub Actions → Hostinger

La version actuellement sur Hostinger est la référence. Installer ces workflows
ne la modifie pas. Après activation, la fusion d’une PR dans `main` déclenche sa mise à jour.
Une PR non fusionnée ne modifie pas la production.

## Circuit, sur le modèle de Thila

1. **Check project** vérifie chaque push sur `main` et chaque pull request.
2. **Deploy to Hostinger** démarre après une fusion dans `main` (événement
   `push` sur `main`). Il reconstruit et valide la production avec PHP 8.3, conformément
   à la plateforme Composer du projet.
3. Le job `package` publie `liranyuzbegi-hostinger-COMMIT`, une archive de code,
   dépendances de production et assets compilés, conservée sept jours sur GitHub.
4. Le job `deploy` utilise l'environnement `production` et cette même archive.
   Il reste ignoré tant que la variable **du dépôt** `HOSTINGER_DEPLOY_ENABLED`
   ne vaut pas `true`.
5. Un lancement manuel depuis **Actions → Run workflow** propose le mode
   **preview** par défaut : il affiche les transferts proposés
   sans modifier les fichiers Hostinger. Le mode **deploy** sauvegarde d'abord
   l'existant, transfère les fichiers, reconstruit le cache sur le serveur et
   vérifie les pages publiques par HTTPS.

Une fusion dans `main` déclenche la livraison ; un push direct sur `main` aussi.
Les branches de PR ne déploient rien. Protéger `main` si seules les fusions de PR
sont autorisées. Un seul workflow de livraison s'exécute à la fois ; un nouveau
lancement n'annule pas le précédent.

## Configuration GitHub

Dans **Settings → Environments**, créer `production` et limiter les branches
autorisées à `main`. Un approbateur peut être ajouté si les options du compte
le permettent.

Ajouter les secrets de cet environnement :

| Secret | Contenu |
| --- | --- |
| `HOSTINGER_SSH_KEY` | Clé privée SSH dédiée à Liran, complète, sans mot de passe |
| `HOSTINGER_KNOWN_HOSTS` | Ligne(s) de clé d'hôte vérifiée(s) pour `[185.166.188.94]:65002` |

Ces noms reprennent ceux de Thila ; leurs valeurs doivent appartenir à Liran.
Ne pas copier la clé privée ni l'hôte de Thila. Aucun secret ne doit être
commité, affiché dans les logs ou envoyé dans une conversation.

Créer une clé dédiée, par exemple dans un terminal local :

```bash
ssh-keygen -t ed25519 -f ~/.ssh/liranyuzbegi_hostinger -C github-actions-liranyuzbegi
```

Laisser la passphrase vide pour l'exécution non interactive. Autoriser sa
clé **publique** sur le compte Hostinger concerné. Cette configuration SSH
est une étape distincte du déploiement ; elle n'a pas été effectuée par la
préparation de ces fichiers.

Pour retrouver la clé d'hôte déjà approuvée sur cet ordinateur :

```bash
ssh-keygen -F '[185.166.188.94]:65002' -f ~/.ssh/known_hosts
```

Copier les lignes de clés sans les commentaires. Si la clé d'hôte n'est pas
encore approuvée, vérifier son empreinte auprès de Hostinger avant de l'ajouter.
Le workflow impose cette vérification, sans approbation automatique.

Enfin, dans **Settings → Secrets and variables → Actions → Variables**, ajouter
la variable **du dépôt** `HOSTINGER_DEPLOY_ENABLED=true` pour autoriser le job
SSH. Une fois activée, chaque fusion dans `main` (ou push direct sur `main`)
déploie automatiquement la version fusionnée. Configurer les secrets et examiner
un aperçu manuel avant d'activer cette variable.

## Cible

```text
Serveur : 185.166.188.94
Port : 65002
Compte : u262228329
Projet : /home/u262228329/domains/liranyuzbegi.com/public_html
URL : https://liranyuzbegi.com
```

Ces valeurs sont propres à Liran dans `scripts/deploy-hostinger.sh`.

## Conservation de la production

- Tous les fichiers `.env*`, les `.htaccess`, `var/` et `public/uploads/` sont
  exclus des transferts. Les médias suivis par Git ne remplacent pas ceux du site.
- Aucune migration, initialisation de données ni création de compte n'est lancée.
  La base de production n'est jamais remplacée par la base locale ou de test.
- Les dépendances sont installées depuis `composer.lock`, sans mise à jour.
- Le CSS à nom stable est copié depuis sa source, en plus des assets compilés.
- Le cache de production est reconstruit sur le serveur avec sa configuration.

## Sauvegarde et limites

Avant le premier transfert du mode `deploy`, l'existant est archivé dans
`/home/u262228329/liranyuzbegi-deploy-backups/`, avec des permissions privées,
hors de la racine web. Cette archive inclut les fichiers de configuration et
les médias, hors caches et logs. Une erreur de sauvegarde bloque le transfert.
Elle ne contient pas de sauvegarde de base de données.

Le site actuel est mis à jour en place : il n'y a pas de réorganisation des
répertoires ni de bascule atomique comme dans l'activation des versions de Thila.
Le transfert ne supprime aucun fichier distant. Une suppression ou un renommage
de fichier PHP demande donc le retrait explicite de l'ancien fichier.

Un échec de transfert, de cache ou de vérification HTTP peut laisser une mise
à jour partielle : examiner les logs et restaurer l'archive manuellement si
nécessaire. Les anciens assets sont conservés. Il n'y a pas de retour arrière
automatique ni de purge automatique des sauvegardes.

## État de la préparation

Les fichiers locaux préparent le mécanisme, sans installer de clé sur Hostinger,
sans créer de secrets GitHub et sans lancer de déploiement. Configurer les accès
avant la fusion si la première fusion doit déployer immédiatement. Sans la
variable d’activation, le job de livraison sera ignoré. Une fois activé, fusionner
une PR signifie autoriser la mise à jour du site avec cette version.

Les simulations du script (aperçu, sauvegarde avant transfert, échec de
sauvegarde, mode invalide, archive manquante) se relancent sans accès distant :

```bash
python3 -B -m unittest discover -s tests/Deployment -v
```
