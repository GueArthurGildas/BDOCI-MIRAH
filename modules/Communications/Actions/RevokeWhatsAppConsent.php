<?php

namespace Modules\Communications\Actions;

use App\Models\ReferenceListValue;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Modules\Communications\Support\WhatsAppConsents;

class RevokeWhatsAppConsent extends Action
{
    public function __construct(private readonly WhatsAppConsents $consents) {}

    public function code(): string
    {
        return 'communications.revoke_whatsapp_consent';
    }

    public function permission(): Permission
    {
        return Permission::CommunicationsSend;
    }

    public function handle(ActionContext $context, array $input): ReferenceListValue
    {
        return $this->consents->revoke($input['number']);
    }
}
