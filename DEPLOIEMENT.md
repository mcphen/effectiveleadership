# Mise en ligne sur l'hébergement cPanel (GoDaddy / CloudLinux)

Le domaine passe par le pare-feu **GoDaddy Website Security (Sucuri)**, qui fournit aussi le certificat HTTPS :

visiteur → Sucuri (HTTPS) → hébergement cPanel `160.153.133.192` → Laravel

Le projet est cloné dans `~/effectiveleadership`, et `~/public_html` devient un lien vers `~/effectiveleadership/public` : seul le dossier `public` est exposé sur le web.

Les fichiers CSS/JS compilés (`public/build`) sont versionnés, car l'hébergement n'a pas Node.js. **Avant chaque commit qui touche `resources/js` ou `resources/css`, lancez `npm run build` et commitez `public/build`.**

## 1. PHP 8.4

- **Site web** : cPanel → **Sélectionner une version de PHP** → **8.4** → **Définir comme actuel**. Dans l'onglet Extensions, cochez au minimum `pdo_mysql`, `mysqlnd`, `pdo`, `mbstring`, `fileinfo`, `intl`, `zip`, `gd`, `dom`, `bcmath`.
- **Terminal** : la commande `php` lance PHP 8.1 par défaut. Ajoutez une fois pour toutes ce raccourci :

```bash
echo "alias php='/opt/alt/php84/usr/bin/php -d extension=mysqlnd.so -d extension=pdo.so -d extension=pdo_mysql.so -d extension=mbstring.so -d extension=fileinfo.so'" >> ~/.bashrc
echo "alias composer='php /opt/alt/php84/usr/bin/composer.phar'" >> ~/.bashrc
source ~/.bashrc
php -v    # 8.4.x
```

## 2. Base de données (cPanel → Bases de données MySQL)

1. Créez la base `elc`. cPanel la renomme `cikbrngkul17_elc`.
2. Créez l'utilisateur `elc`, qui devient `cikbrngkul17_elc`, avec un mot de passe fort.
3. Ajoutez l'utilisateur à la base avec **Tous les privilèges**.

## 3. Installation

```bash
cd ~ && git clone https://github.com/mcphen/effectiveleadership.git
cd ~/effectiveleadership
composer install --no-dev --optimize-autoloader
cp .env.example .env
nano .env
```

Dans `.env`, modifiez les lignes suivantes, puis enregistrez avec Ctrl+O, Entrée, Ctrl+X. Mettez entre guillemets toute valeur qui contient `#`, `$` ou une espace.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://effectiveleadership.consulting
LOG_LEVEL=error

DB_DATABASE=cikbrngkul17_elc
DB_USERNAME=cikbrngkul17_elc
DB_PASSWORD="mot-de-passe-de-la-base"

SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=sync

ADMIN_EMAIL=adresse-de-connexion@exemple.com
ADMIN_PASSWORD="un-mot-de-passe-fort"
```

```bash
php artisan key:generate --force
php artisan migrate --seed --force    # tables + administrateur + contenu initial du site
php artisan storage:link
chmod -R 775 storage bootstrap/cache
php artisan optimize
```

## 4. Brancher le site sur le domaine

```bash
cd ~
mv public_html public_html_ancien
ln -s ~/effectiveleadership/public public_html
```

## 5. Côté pare-feu (Website Security)

- **Paramètres → Général → Adresse IP d'hébergement** : uniquement `160.153.133.192`.
- **HTTPS/SSL** : mode **Full HTTPS**, avec la redirection HTTP → HTTPS activée.
- **Performance → Vider le cache** après la mise en ligne.

## 6. Vérifier

- https://effectiveleadership.consulting doit rediriger vers `/fr` ou `/en`.
- https://effectiveleadership.consulting/login : connectez-vous avec `ADMIN_EMAIL` et `ADMIN_PASSWORD`.
- Un message envoyé depuis la page Contact doit apparaître dans **Admin → Messages**.
- En cas d'erreur 500, consultez le journal : `tail -50 ~/effectiveleadership/storage/logs/laravel.log`.

Après une modification dans l'admin, si le site public ne change pas, videz le cache Sucuri.

## Mises à jour

Sur le poste de développement : `npm run build`, commit, puis `git push`. Ensuite, sur le serveur :

```bash
cd ~/effectiveleadership && git pull && composer install --no-dev -o && php artisan migrate --force && php artisan optimize
```

`.env` et les fichiers chargés depuis l'admin (`storage/app`) ne sont pas dans git : `git pull` les laisse intacts. **Ne relancez jamais `db:seed` en production**, car cela remplacerait le contenu modifié dans l'admin.

## Notifications par e-mail (facultatif)

Pour recevoir les messages du formulaire de contact par e-mail, avec une boîte Microsoft 365 du domaine :

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=boite@effectiveleadership.consulting
MAIL_PASSWORD="mot-de-passe-de-la-boite"
MAIL_FROM_ADDRESS=boite@effectiveleadership.consulting
```

L'option « SMTP authentifié » doit être activée pour cette boîte dans le centre d'administration Microsoft 365. Après toute modification du `.env`, lancez `php artisan optimize`. Même sans e-mail, les messages restent enregistrés dans l'admin.
