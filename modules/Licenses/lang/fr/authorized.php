<?php

return [
    'title' => 'Navires autorisés',
    'subtitle' => 'Liste calculée à partir des licences actives (RG01)',
    'count' => 'Navires autorisés',
    'last_publication' => 'Dernière publication',
    'version' => 'Version :version',
    'never' => 'Jamais publiée',
    'freshness' => 'Fraîcheur',
    'stale' => 'À republier',
    'up_to_date' => 'À jour',
    'list_title' => 'Liste courante',
    'publish' => 'Publier une nouvelle version',
    'history_title' => 'Publications',
    'version_column' => 'Version',
    'published_at' => 'Publiée le',
    'download' => 'Télécharger',
    'published' => 'La liste a été publiée.',
    'pdf' => [
        'title' => 'Liste des navires autorisés, version :version',
        'published_at' => 'Publiée le :date, :count navires',
    ],
    'how_title' => 'Comment cette liste est calculée (règle RG01)',
    'how_rule' => 'Un navire est autorisé lorsqu\'il détient une licence au statut « active » dont la date d\'expiration n\'est pas dépassée.',
    'how_one_per_vessel' => 'Un navire n\'apparaît qu\'une fois : si plusieurs licences répondent à la règle, celle qui expire le plus tard est retenue.',
    'how_excluded' => 'Les licences suspendues, révoquées, expirées ou pas encore actives ne comptent pas : le navire quitte la liste dès le changement de statut ou le lendemain de l\'expiration.',
    'how_publication' => 'La liste courante est recalculée à chaque ouverture ; la version publiée est une copie figée, à republier quand l\'indicateur « Fraîcheur » le demande.',
];
