<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tables du CMS ELC.
 *
 * Les colonnes JSON « traduisibles » contiennent {"fr": "...", "en": "..."}.
 * Les colonnes « blocks » contiennent la liste ordonnée des blocs de la page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('editor')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('filename');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->json('alt')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->json('blocks')->nullable();
            $table->json('seo')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_home')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('location')->unique();
            $table->string('name');
            $table->json('items')->nullable();
            $table->timestamps();
        });

        Schema::create('solution_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('solution_categories')->nullOnDelete();
            $table->json('title');
            $table->json('slug');
            $table->json('summary')->nullable();
            $table->string('image')->nullable();
            $table->json('blocks')->nullable();
            $table->json('seo')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('solutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solution_category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('title');
            $table->json('slug');
            $table->json('subtitle')->nullable();
            $table->json('signature')->nullable();
            $table->json('summary')->nullable();
            $table->string('image')->nullable();
            $table->json('blocks')->nullable();
            $table->json('seo')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->string('client')->nullable();
            $table->json('sector')->nullable();
            $table->json('country')->nullable();
            $table->string('year')->nullable();
            $table->json('summary')->nullable();
            $table->string('image')->nullable();
            $table->json('blocks')->nullable();
            $table->json('seo')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('client_references', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('url')->nullable();
            $table->json('sector')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author');
            $table->json('role')->nullable();
            $table->string('company')->nullable();
            $table->json('quote');
            $table->string('photo')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('article');
            $table->json('title');
            $table->json('slug');
            $table->json('excerpt')->nullable();
            $table->string('cover')->nullable();
            $table->string('attachment')->nullable();
            $table->string('author')->nullable();
            $table->json('blocks')->nullable();
            $table->json('seo')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->string('location')->nullable();
            $table->json('contract_type')->nullable();
            $table->json('summary')->nullable();
            $table->json('blocks')->nullable();
            $table->json('seo')->nullable();
            $table->date('closes_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('locale', 5)->default('fr');
            $table->string('page_url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_offer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('locale', 5)->default('fr');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('locale', 5)->default('fr');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'newsletter_subscribers', 'job_applications', 'contact_messages', 'job_offers', 'posts',
            'testimonials', 'client_references', 'case_studies', 'solutions', 'solution_categories',
            'menus', 'pages', 'media', 'settings',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
