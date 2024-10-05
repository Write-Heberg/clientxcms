<?php
return [
    'title' => 'Extensions',
    'description' => 'Gérez les extensions de CLIENTXCMS, activez ou désactivez les extensions installées.',
    'settings' => [
        'title' => 'Paramètres des extensions',
        'description' => 'Gérez les paramètres des extensions installées',
        'clearcache' => 'Vider le cache',
        'enable' => 'Activer',
        'disable' => 'Désactiver',
        'enabled' => 'Activée',
        'disabled' => 'Désactivée',
        'activable' => 'Activable',
        'notactivable' => 'Non activable',
        'buy' => 'Acheter',
    ],
    'onetime' => 'Une seule fois',
    'monthly' => 'Mensuel',
    'flash' => [
        'already_enabled' => 'L\'extension est déjà activée.',
        'extension_not_enabled' => 'L\'extension :extension n\'est pas activée et est requis pour cette extension.',
        'cannot_enable' => 'Impossible d\'activer l\'extension.',
        'composer_not_found' => 'Le fichier composer.json n\'a pas été trouvé.',
        'extension_not_loaded' => 'L\'extension :extension est requise pour l\'activation de cette extension.',
        'extension_not_installed' => 'L\'extension :extension n\'est pas installée.',
        'extension_version_not_compatible' => 'La version de l\'extension :extension (:current) n\'est pas compatible avec la version requise (:version).',
        'cache_cleared' => 'Le cache a été vidé avec succès.',
    ],
];
