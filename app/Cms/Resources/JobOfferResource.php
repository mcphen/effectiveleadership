<?php

namespace App\Cms\Resources;

use App\Models\JobOffer;

class JobOfferResource extends Resource
{
    public static string $model = JobOffer::class;

    public static string $slug = 'jobs';

    public static string $label = 'Offres d\'emploi';

    public static string $singular = 'offre d\'emploi';

    public static bool $feminine = true;

    public static string $icon = 'user-plus';

    public static function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'label' => 'Intitulé du poste', 't' => true, 'required' => true],
            ['name' => 'summary', 'type' => 'textarea', 'label' => 'Résumé', 't' => true, 'rows' => 3],
            ['name' => 'location', 'type' => 'text', 'label' => 'Lieu', 'width' => 'half', 'default' => 'Dakar, Sénégal'],
            ['name' => 'contract_type', 'type' => 'text', 'label' => 'Type de contrat', 't' => true, 'width' => 'half'],
            ['name' => 'blocks', 'type' => 'blocks', 'label' => 'Description du poste'],
            ['name' => 'is_published', 'type' => 'toggle', 'label' => 'Publiée', 'default' => false, 'side' => true],
            ['name' => 'closes_at', 'type' => 'date', 'label' => 'Date limite de candidature', 'side' => true],
            static::slugField(),
            static::seoField(),
        ];
    }

    public static function columns(): array
    {
        return [
            ['name' => 'title', 'label' => 'Poste', 'type' => 'title'],
            ['name' => 'location', 'label' => 'Lieu', 'type' => 'text'],
            ['name' => 'closes_at', 'label' => 'Date limite', 'type' => 'date'],
            ['name' => 'applications_count', 'label' => 'Candidatures', 'type' => 'text', 'value' => fn ($m) => $m->applications()->count()],
            ['name' => 'is_published', 'label' => 'Publiée', 'type' => 'bool'],
        ];
    }
}
