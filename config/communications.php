<?php

return [
    'dispatch_enabled' => (bool) env('COMMUNICATIONS_DISPATCH_ENABLED', true),
    'default_daily_cap' => (int) env('COMMUNICATIONS_DEFAULT_DAILY_CAP', 3),
    'whatsapp_driver' => env('COMMUNICATIONS_WHATSAPP_DRIVER', 'fake'),
    'whatsapp_consent_list' => 'whatsapp_consents',
    'ai_agent_email' => env('COMMUNICATIONS_AI_AGENT_EMAIL', 'ai-agent@example.com'),
    'system_actor_email' => env('COMMUNICATIONS_SYSTEM_ACTOR_EMAIL', 'system@example.com'),
    'webhook_secret' => env('COMMUNICATIONS_WEBHOOK_SECRET'),
    'sender_name' => env('COMMUNICATIONS_SENDER_NAME', 'CSP'),

    'alerts' => [
        'prepared_messages' => ['enabled' => true, 'cooldown_hours' => 4],
    ],
];
