<?php

return [
    'message_received' => [
        'title' => 'Message reçu',
        'message' => 'Vous avez reçu un message : :subject',
    ],
    'note' => [
        'to_check' => [
            'title' => 'Note : à vérifier',
            'message' => ':author vous a laissé une note (à vérifier) concernant :vessel.',
        ],
        'suspect' => [
            'title' => 'Note : suspect',
            'message' => ':author vous a laissé une note (suspect) concernant :vessel.',
        ],
        'reminder' => [
            'title' => 'Note : rappel',
            'message' => ':author vous a laissé une note (rappel) concernant :vessel.',
        ],
        'to_forward' => [
            'title' => 'Note : à transmettre',
            'message' => ':author vous a laissé une note (à transmettre) concernant :vessel.',
        ],
    ],
    'prepared_messages' => [
        'title' => 'Messages à valider',
        'message' => '{1} :count message attend une validation.|[2,*] :count messages attendent une validation.',
    ],
];
