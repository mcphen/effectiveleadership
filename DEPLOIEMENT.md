# Mise en ligne sur l'hébergement cPanel (GoDaddy)

Le domaine passe par le pare-feu **GoDaddy Website Security (Sucuri)**, qui fournit le certificat HTTPS :

visiteur → Sucuri (HTTPS) → hébergement cPanel `160.153.133.192` → Laravel

L'application est installée dans `~/elc`. Le dossier `~/public_html` devient un lien vers `~/elc/public`, si bien que seul le dossier `public` est exposé sur le web.

## 1. Préparer l'archive (sur le poste de développement)

```bash
bash scripts/build-deploy.sh
```

Le script produit `../elc-deploy.tar.gz`, qui contient le code du dernier commit, `vendor` (sans les dépendances de développement) et `public/build`. Il est inutile d'installer Node ou Composer sur le serveur.

## 2. Vérifier PHP sur le serveur (Terminal cPanel)

```bash
php -v        # doit afficher 8.4.x
php -m | grep -iE 'pdo_mysql|mbstring|fileinfo|openssl|tokenizer|xml|ctype|curl'
```

Si la version n'est pas 8.4, choisissez **PHP 8.4** dans cPanel, sous **MultiPHP Manager** (ou **Sélectionner une version de PHP**), puis fermez et rouvrez le Terminal. Si `php -v` affiche toujours une autre version :

```bash
ls /opt/cpanel/ea-php84/root/usr/bin/php /opt/alt/php84/usr/bin/php 2>/dev/null
echo "alias php=/opt/cpanel/ea-php84/root/usr/bin/php" >> ~/.bashrc && source ~/.bashrc   # adapter au chemin trouvé
```

## 3. Créer la base de données (cPanel → Bases de données MySQL)

1. Créez une base, par exemple `elc`. cPanel la préfixe : `cikbrngkul17_elc`.
2. Créez un utilisateur avec un mot de passe fort, par exemple `cikbrngkul17_elc`.
3. **Ajoutez l'utilisateur à la base** en cochant **Tous les privilèges**.

## 4. Déposer et installer

Envoyez `elc-deploy.tar.gz` dans le dossier personnel, **pas** dans `public_html`, avec le Gestionnaire de fichiers → Charger. Puis, dans le Terminal :

```bash
cd ~
mkdir -p elc && tar -xzf elc-deploy.tar.gz -C elc && rm elc-deploy.tar.gz
cd ~/elc
cp .env.example .env
nano .env
```

Dans `.env`, modifiez ces lignes, puis enregistrez avec Ctrl+O, Entrée, Ctrl+X :

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://effectiveleadership.consulting
LOG_LEVEL=error

DB_DATABASE=cikbrngkul17_elc
DB_USERNAME=cikbrngkul17_elc
DB_PASSWORD=le-mot-de-passe-de-l-etape-3

SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=sync

ADMIN_EMAIL=adresse-de-connexion@exemple.com
ADMIN_PASSWORD=un-mot-de-passe-fort
```

Lancez ensuite l'installation :

```bash
php artisan key:generate --force
php artisan migrate --seed --force    # tables + administrateur + contenu initial du site
php artisan storage:link
chmod -R 775 storage bootstrap/cache
php artisan optimize
```

## 5. Brancher le site sur le domaine

```bash
cd ~
grep -A4 "php -- BEGIN" public_html/.htaccess    # note le bloc PHP éventuel ajouté par cPanel
mv public_html public_html_ancien
ln -s ~/elc/public public_html
```

Si la commande `grep` a affiché un bloc `# php -- BEGIN cPanel-generated handler…`, rouvrez **MultiPHP Manager** et réappliquez **PHP 8.4** au domaine. cPanel réécrira alors ce bloc dans `~/elc/public/.htaccess`.

## 6. Côté pare-feu (Website Security)

- **Paramètres → Général → Adresse IP d'hébergement** : uniquement `160.153.133.192`.
- **HTTPS/SSL** : mode **Full HTTPS**, avec la redirection HTTP → HTTPS activée.
- **Performance → Vider le cache** après la mise en ligne.

## 7. Vérifier

- https://effectiveleadership.consulting doit rediriger vers `/fr` ou `/en`.
- https://effectiveleadership.consulting/login : connectez-vous avec `ADMIN_EMAIL` et `ADMIN_PASSWORD`.
- Envoyez un message de test depuis la page Contact : il doit apparaître dans **Admin → Messages**.
- En cas de page blanche ou d'erreur 500, consultez le journal : `tail -50 ~/elc/storage/logs/laravel.log`.

Après une modification dans l'admin, si le site public ne change pas, videz le cache Sucuri.

## Notifications par e-mail (facultatif)

Pour recevoir les messages du formulaire de contact par e-mail, avec une boîte Microsoft 365 du domaine :

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_SCHEME=null
MAIL_USERNAME=boite@effectiveleadership.consulting
MAIL_PASSWORD=mot-de-passe-de-la-boite
MAIL_FROM_ADDRESS=boite@effectiveleadership.consulting
```

L'option « SMTP authentifié » doit être activée pour cette boîte dans le centre d'administration Microsoft 365. Après toute modification du `.env`, lancez `php artisan optimize`. Même sans e-mail, les messages restent enregistrés dans l'admin.

## Mises à jour ultérieures

```bash
# sur le poste de développement
bash scripts/build-deploy.sh
# sur le serveur, après avoir chargé la nouvelle archive dans ~
cd ~ && tar -xzf elc-deploy.tar.gz -C elc && rm elc-deploy.tar.gz
cd ~/elc && php artisan migrate --force && php artisan optimize
```

L'archive ne contient ni `.env` ni les fichiers chargés depuis l'admin (`storage/app`) : ils sont conservés. **Ne relancez jamais `db:seed` en production**, car cela remplacerait le contenu modifié dans l'admin.
