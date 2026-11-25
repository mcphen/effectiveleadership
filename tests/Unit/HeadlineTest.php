<?php

use App\Support\Headline;

test('les grands titres prennent une majuscule à chaque mot important', function (string $text, string $locale, string $expected) {
    expect(Headline::title($text, $locale))->toBe($expected);
})->with([
    'exemple du client' => ['Un cabinet de conseil en leadership et management', 'fr', 'Un Cabinet de Conseil en Leadership et Management'],
    'trait d\'union' => ['Qui sommes-nous', 'fr', 'Qui Sommes-Nous'],
    'élision' => ['Créer de l’impact', 'fr', 'Créer de l’Impact'],
    'une phrase par ligne' => ["Développer des leaders.\nTransformer les organisations.", 'fr', "Développer des Leaders.\nTransformer les Organisations."],
    'après deux-points' => ['ELC en chiffres : un impact mesurable', 'fr', 'ELC en Chiffres : Un Impact Mesurable'],
    'contraction anglaise' => ['Let\'s talk about your challenges', 'en', 'Let\'s Talk About Your Challenges'],
    'petits mots anglais' => ['A leadership and management consulting firm', 'en', 'A Leadership and Management Consulting Firm'],
    'marques et sigles inchangés' => ['AXIOME™ by ELC', 'en', 'AXIOME™ by ELC'],
    'marque anglaise dans un titre français' => ['Découvrez AXIOME™ by ELC', 'fr', 'Découvrez AXIOME™ by ELC'],
]);

test('chaque langue d\'un titre traduisible est traitée avec ses propres règles', function () {
    expect(Headline::translations(['en' => 'what guides our work', 'fr' => 'ce qui guide notre action']))
        ->toBe(['en' => 'What Guides Our Work', 'fr' => 'Ce Qui Guide Notre Action']);
});
