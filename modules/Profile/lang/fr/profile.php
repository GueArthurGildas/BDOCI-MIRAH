<?php

return [
    'title' => 'Mon profil',
    'subtitle' => 'Mes informations, mes messages, mes alertes et mes activités en attente',
    'link' => 'Mon profil',
    'empty' => 'Rien à afficher pour le moment.',
    'fields' => [
        'first_name' => 'Prénom',
        'name' => 'Nom',
        'job_title' => 'Fonction',
        'role' => 'Rôle',
        'team' => 'Équipe',
        'email' => 'E-mail',
        'phone' => 'Téléphone',
        'whatsapp_number' => 'Numéro WhatsApp',
    ],
    'photo' => [
        'field' => 'photo',
        'change' => 'Changer la photo',
        'remove' => 'Retirer la photo',
        'updated' => 'Votre photo a été mise à jour.',
        'removed' => 'Votre photo a été retirée.',
    ],
    'cards' => [
        'messages' => 'Messages non lus',
        'urgencies' => 'Urgences',
        'alerts' => 'Alertes en cours',
        'pending' => 'Activités en attente',
    ],
    'columns' => [
        'date' => 'Date',
        'from' => 'De',
        'urgency' => 'Urgence',
        'vessel' => 'Navire',
        'message' => 'Message',
        'kind' => 'Nature',
        'detail' => 'Détail',
        'category' => 'Catégorie',
        'status' => 'Statut',
    ],
    'messages' => [
        'title' => 'Mes messages',
        'unread' => '{0} Aucun message non lu|{1} :count message non lu|[2,*] :count messages non lus',
        'all' => 'Voir tous les messages',
    ],
    'urgencies' => [
        'title' => 'Mes urgences',
        'hint' => 'Alertes urgentes et messages urgents non lus qui me concernent',
        'alert' => 'Alerte',
        'message' => 'Message',
    ],
    'alerts' => [
        'title' => 'Mes alertes',
        'hint' => 'Alertes en cours classées : urgences, utiles à mes tâches, autres',
    ],
    'pending' => [
        'title' => 'Mes activités en attente',
        'hint' => 'Observations à suivre, messages à traiter avant une échéance et opérations que j\'ai ouvertes',
        'observation' => 'Observation',
        'note' => 'Message à traiter',
        'operation' => 'Opération',
    ],
];
