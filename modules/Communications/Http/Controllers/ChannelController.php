<?php

namespace Modules\Communications\Http\Controllers;

use App\Enums\CommunicationChannelCode;
use App\Http\Controllers\Controller;
use App\Models\CommunicationChannel;
use App\Models\EmailConnector;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Communications\Actions\RecordWhatsAppConsent;
use Modules\Communications\Actions\RevokeWhatsAppConsent;
use Modules\Communications\Actions\SaveEmailConnector;
use Modules\Communications\Actions\UpdateCommunicationChannel;
use Modules\Communications\Http\Requests\SaveEmailConnectorRequest;
use Modules\Communications\Http\Requests\UpdateChannelRequest;
use Modules\Communications\Http\Requests\WhatsAppConsentRequest;
use Modules\Communications\Support\WhatsAppConsents;

class ChannelController extends Controller
{
    public function index(WhatsAppConsents $consents): View
    {
        return view('communications::channels.index', [
            'channels' => collect(CommunicationChannelCode::cases())->map(fn (CommunicationChannelCode $code) => CommunicationChannel::forCode($code)),
            'connector' => EmailConnector::latest('id')->first() ?? new EmailConnector(['is_active' => false]),
            'mailers' => array_combine(array_keys(config('mail.mailers')), array_keys(config('mail.mailers'))),
            'consents' => $consents->granted(),
        ]);
    }

    public function updateChannel(CommunicationChannel $channel, UpdateChannelRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(UpdateCommunicationChannel::class, $request->user(), [...$request->validated(), 'id' => $channel->id]);

        return to_route('communications.channels.index')->with('status', __('communications::channels.saved'));
    }

    public function updateConnector(SaveEmailConnectorRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveEmailConnector::class, $request->user(), $request->validated());

        return to_route('communications.channels.index')->with('status', __('communications::channels.connector_saved'));
    }

    public function storeConsent(WhatsAppConsentRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordWhatsAppConsent::class, $request->user(), $request->validated());

        return to_route('communications.channels.index')->with('status', __('communications::channels.consent_recorded'));
    }

    public function revokeConsent(WhatsAppConsentRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RevokeWhatsAppConsent::class, $request->user(), $request->validated());

        return to_route('communications.channels.index')->with('status', __('communications::channels.consent_revoked'));
    }
}
