# Site Effective Leadership Consulting (ELC)

Site bilingue (FR / EN) de **Effective Leadership Consulting**, Dakar, avec une administration où tout le contenu se modifie sans toucher au code : pages construites par blocs, menus, médiathèque, offres, références, publications, offres d'emploi et réglages (logo, couleurs, polices, coordonnées).

## Installation

```bash
composer install
npm install
cp .env.example .env        # puis renseigner la base de données et ADMIN_*
php artisan key:generate
php artisan migrate --seed  # crée l'administrateur + tout le contenu initial
php artisan storage:link
npm run build               # ou « npm run dev » pendant le développement
```

- Site : `http://elc.test` → redirige vers `/fr` ou `/en` selon la langue du navigateur.
- Administration : `http://elc.test/login`. Les identifiants sont définis par `ADMIN_EMAIL` et `ADMIN_PASSWORD`.
- Tests : `php vendor/bin/pest` (base MySQL `elc_test`).

Pour réinitialiser le contenu à partir des documents du client : `php artisan db:seed --class=ContentSeeder`. **Attention, cette commande efface les pages, offres, menus et références existants.**

## Architecture

| Partie | Technologie | Emplacement |
|---|---|---|
| Site public | Blade + Tailwind + Alpine (HTML rendu par le serveur, bon pour le référencement) | `resources/views/site`, `resources/views/blocks`, `resources/css/site.css`, `resources/js/site.ts` |
| Administration | Inertia + Vue 3 | `resources/js/pages/admin`, `resources/js/components/cms` |
| Contenu | Modèles Eloquent, champs traduisibles en JSON `{"fr": "…", "en": "…"}` (spatie/laravel-translatable) | `app/Models` |
| Moteur du CMS | Catalogue de blocs, types de champs, types de contenu | `app/Cms` |

### Principes

- **Un bloc est décrit une seule fois**, dans `app/Cms/Blocks.php`. L'admin génère son formulaire à partir de cette description, et le site l'affiche avec `resources/views/blocks/{type}.blade.php`.
- **Un type de contenu = une classe** dans `app/Cms/Resources`. Le contrôleur et les écrans Vue sont génériques : liste, création, modification, duplication, réordonnancement.
- **Liens internes** : les liens choisis dans l'admin sont enregistrés sous la forme `page:12`, `solution:4`… et transformés en URL au moment de l'affichage. Ils suivent donc les changements d'adresse et de langue.
- **Repli linguistique** : un texte non traduit en anglais s'affiche en français. Le tableau de bord liste les contenus à traduire.
- **URL** : `/fr/{page}`, `/fr/solutions/{catégorie}/{offre}`, `/fr/realisations/{…}` ↔ `/en/case-studies/{…}`, `/fr/insights/{…}`, `/fr/carrieres/{…}`. Les préfixes traduits sont réglés dans `config/elc.php`.

### Ajouter un nouveau type de bloc

1. Le déclarer dans `Blocks::all()` : libellé, groupe, icône et champs.
2. Créer `resources/views/blocks/{type}.blade.php`. Les données arrivent dans `$d`, les réglages d'apparence dans `$s`.
3. Si l'icône d'admin est nouvelle, l'ajouter dans `resources/js/components/cms/AdminIcon.vue`.

### Icônes du site

Les icônes du site public et du champ « icône » viennent de Lucide. Pour en ajouter : compléter la liste dans `scripts/build-icons.mjs`, puis lancer `node scripts/build-icons.mjs`.

## Sécurité

- Pas d'inscription publique : les comptes se créent dans **Utilisateurs**, réservé au rôle administrateur.
- Le HTML saisi dans l'éditeur est filtré (`App\Cms\Html`) ; les formulaires publics sont limités en fréquence et protégés par un champ piège anti-robots.
- Les CV reçus sont stockés hors du dossier public (`storage/app/private/applications`) et téléchargeables uniquement depuis l'admin.
- Les notifications par e-mail des messages et candidatures nécessitent une configuration `MAIL_*` valide.
