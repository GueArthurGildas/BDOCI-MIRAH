<?php

return [
    'title' => 'Groupes de permissions',
    'subtitle' => 'Les groupes ajoutent des permissions aux utilisateurs en plus de leur rôle',
    'list_title' => 'Groupes',
    'new' => 'Nouveau groupe',
    'create_title' => 'Nouveau groupe',
    'edit_title' => 'Modifier le groupe',
    'name' => 'Groupe',
    'label' => 'Nom du groupe',
    'description' => 'Description',
    'permissions' => 'Permissions accordées par le groupe',
    'members' => 'Membres',
    'members_count' => 'Membres',
    'permissions_count' => 'Permissions',
    'confirm_delete' => 'Supprimer le groupe :group ?',
    'created' => 'Le groupe a été créé.',
    'updated' => 'Le groupe a été mis à jour.',
    'deleted' => 'Le groupe a été supprimé.',
    'cannot_delete_system' => 'Un groupe système ne peut pas être supprimé.',
    'system' => [
        'fnp_license_management' => [
            'label' => 'Gestion FNP et licences',
            'description' => 'Enregistre les licences, leurs dates, suspensions et révocations.',
        ],
        'infraction_investigation' => [
            'label' => 'Instruction des infractions',
            'description' => 'Prépare et suit les dossiers d\'infraction.',
        ],
        'surveillance_opinion' => [
            'label' => 'Avis surveillance',
            'description' => 'Donne l\'avis de surveillance qui revenait au sous-directeur de la surveillance.',
        ],
        'automatic_dispatch' => [
            'label' => 'Gestion des envois automatiques',
            'description' => 'Règle la fréquence et l\'activation des envois automatiques.',
        ],
        'trigger_ai_agent' => [
            'label' => 'Déclencher l\'agent IA',
            'description' => 'Peut déclencher l\'agent IA.',
        ],
    ],
];
