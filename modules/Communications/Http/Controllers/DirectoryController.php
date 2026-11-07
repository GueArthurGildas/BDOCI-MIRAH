<?php

namespace Modules\Communications\Http\Controllers;

use App\Enums\CommunicationChannelCode;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Country;
use App\Models\LogbookDay;
use App\Models\MessageTemplate;
use App\Models\Observation;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\GeneratedDocuments;
use App\Support\Tables\PerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Communications\Actions\NotifyContacts;
use Modules\Communications\Http\Requests\NotifyContactsRequest;
use Modules\Communications\Support\MessageRenderer;

class DirectoryController extends Controller
{
    private const PER_PAGE = 10;

    private const RECENT_DAYS = 60;

    private const RECENT_OBSERVATIONS = 100;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'integer', 'exists:countries,id'],
        ]);

        $contacts = Contact::query()
            ->with(['country', 'institution'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $term) => $query->where(fn (Builder $inner) => $inner
                ->whereLike('organization', "%{$term}%")
                ->orWhereLike('email', "%{$term}%")
                ->orWhereLike('phone', "%{$term}%")))
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => $query->where('category', $category))
            ->when($filters['country'] ?? null, fn (Builder $query, int|string $country) => $query->where('country_id', $country))
            ->orderBy('organization')
            ->paginate(PerPage::from($request, self::PER_PAGE))
            ->withQueryString();

        return view('communications::directory.index', [
            'contacts' => $contacts,
            'filters' => $filters,
            'categories' => Contact::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category', 'category')->all(),
            'countries' => Country::query()->whereIn('id', Contact::query()->whereNotNull('country_id')->select('country_id'))->orderBy('label')->pluck('label', 'id')->all(),
        ]);
    }

    public function compose(Request $request, MessageRenderer $renderer): View
    {
        $data = $request->validate([
            'contacts' => ['required', 'array', 'min:1', 'max:'.NotifyContactsRequest::MAX_CONTACTS],
            'contacts.*' => ['integer', 'exists:contacts,id'],
            'channel' => ['nullable', Rule::in(array_map(fn (CommunicationChannelCode $channel) => $channel->value, NotifyContactsRequest::CHANNELS))],
            'template' => ['nullable', Rule::exists('message_templates', 'id')->where('is_active', true)],
            'vessel' => ['nullable', Rule::exists('vessels', 'id')->whereNull('deleted_at')],
        ]);

        $template = ! empty($data['template']) ? MessageTemplate::find($data['template']) : null;
        $vessel = ! empty($data['vessel']) ? Vessel::find($data['vessel']) : null;
        $rendered = $template ? $renderer->render($template, $vessel) : ['subject' => null, 'body' => ''];

        return view('communications::directory.compose', [
            'contacts' => Contact::whereIn('id', $data['contacts'])->with('country')->orderBy('organization')->get(),
            'channels' => collect(CommunicationChannelCode::options())->only(array_map(fn (CommunicationChannelCode $channel) => $channel->value, NotifyContactsRequest::CHANNELS))->all(),
            'channel' => $data['channel'] ?? ($template?->channel?->value === CommunicationChannelCode::WhatsApp->value ? 'whatsapp' : 'email'),
            'template' => $template,
            'vessel' => $vessel,
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
            'templates' => MessageTemplate::where('is_active', true)->orderBy('code')->get()->mapWithKeys(fn (MessageTemplate $item) => [$item->id => "{$item->code} ({$item->channel->value})"])->all(),
            'vessels' => Vessel::orderBy('name')->get(['id', 'name', 'registration_number'])->mapWithKeys(fn (Vessel $item) => [$item->id => "{$item->name} ({$item->registration_number})"])->all(),
            'days' => LogbookDay::query()->latest('day')->limit(self::RECENT_DAYS)->get()->mapWithKeys(fn (LogbookDay $day) => [$day->id => $day->day->isoFormat('dddd L')])->all(),
            'observations' => Observation::query()->with('vessel')->latest('observed_at')->limit(self::RECENT_OBSERVATIONS)->get()->mapWithKeys(fn (Observation $observation) => [$observation->id => '#'.$observation->id.' · '.$observation->observed_at->isoFormat('L').($observation->vessel ? ' · '.$observation->vessel->name : '')])->all(),
            'kinds' => GeneratedDocuments::KINDS,
        ]);
    }

    public function send(NotifyContactsRequest $request, ActionRunner $actions): RedirectResponse
    {
        $result = $actions->run(NotifyContacts::class, $request->user(), $request->payload());

        $redirect = to_route('communications.messages.index')->with('status', trans_choice('communications::directory.sent', $result->messages->count(), ['count' => $result->messages->count()]));

        if ($result->skipped !== []) {
            $redirect->with('error', __('communications::directory.skipped', ['list' => collect($result->skipped)->map(fn (array $skip) => $skip['contact']->organization.' ('.__("communications::directory.reasons.{$skip['reason']}").')')->implode(', ')]));
        }

        return $redirect;
    }
}
