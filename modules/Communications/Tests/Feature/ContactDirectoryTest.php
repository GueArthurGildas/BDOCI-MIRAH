<?php

namespace Modules\Communications\Tests\Feature;

use App\Enums\MessageStatus;
use App\Models\Contact;
use App\Models\Country;
use App\Models\Document;
use App\Models\LogbookDay;
use App\Models\MessageTemplate;
use App\Models\Observation;
use App\Models\SentMessage;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use App\Support\Documents\GeneratedDocuments;
use Database\Seeders\AmrepMessageTemplatesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Modules\Communications\Channels\WhatsAppGateway;
use Modules\Communications\Support\WhatsAppConsents;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class ContactDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(DocumentStore::DISK);
    }

    private function payload(iterable $contacts, array $overrides = []): array
    {
        return array_merge([
            'contacts' => collect($contacts)->map->id->all(),
            'channel' => 'email',
            'subject' => 'Notification personnalisée',
            'body' => 'Texte modifié librement pour tous les contacts.',
        ], $overrides);
    }

    /** @return array<int, Email> */
    private function sentEmails(): array
    {
        return collect(Mail::mailer('array')->getSymfonyTransport()->messages())->map(fn ($sent) => $sent->getOriginalMessage())->all();
    }

    public function test_guests_are_redirected_and_administrators_are_forbidden(): void
    {
        $this->get(route('communications.directory.index'))->assertRedirect(route('login'));

        $this->signInAs(Role::Admin);
        $this->get(route('communications.directory.index'))->assertForbidden();
    }

    public function test_the_directory_is_filtered_by_text_category_and_country(): void
    {
        $this->signInAs(Role::Agent);
        $benin = Country::firstOrCreate(['iso2_code' => 'BJ'], ['iso3_code' => 'BEN', 'label' => 'Bénin']);
        $ghana = Country::firstOrCreate(['iso2_code' => 'GH'], ['iso3_code' => 'GHA', 'label' => 'Ghana']);
        $first = Contact::factory()->create(['organization' => 'Direction des Pêches du Bénin', 'category' => 'État du pavillon', 'country_id' => $benin->id, 'email' => 'peches@benin.test']);
        Contact::factory()->create(['organization' => 'Fisheries Commission', 'category' => 'État côtier', 'country_id' => $ghana->id, 'email' => 'fc@ghana.test']);

        $ids = fn (array $filters) => $this->get(route('communications.directory.index', $filters))->assertOk()->viewData('contacts')->pluck('id')->all();

        $this->assertSame([$first->id], $ids(['search' => 'Bénin']));
        $this->assertSame([$first->id], $ids(['search' => 'peches@benin']));
        $this->assertSame([$first->id], $ids(['category' => 'État du pavillon']));
        $this->assertSame([$first->id], $ids(['country' => $benin->id]));
        $this->assertCount(2, $ids([]));
        $this->get(route('communications.directory.index', ['country' => 999999]))->assertSessionHasErrors('country');
    }

    public function test_the_directory_lists_ten_contacts_per_page_and_offers_the_selection(): void
    {
        $this->signInAs(Role::Agent);
        Contact::factory()->count(12)->create();

        $this->get(route('communications.directory.index'))
            ->assertViewHas('contacts', fn ($contacts) => $contacts->perPage() === 10 && $contacts->count() === 10 && $contacts->total() === 12)
            ->assertSee('data-gs-check-all', false)
            ->assertSee(__('communications::directory.notify_selection'));
    }

    public function test_composing_prefills_the_message_from_a_template_and_a_vessel(): void
    {
        $this->signInAs(Role::Agent);
        $contact = Contact::factory()->create();
        $vessel = Vessel::factory()->create(['name' => 'NAVIRE AMREP']);
        $template = MessageTemplate::factory()->create(['subject' => 'Avis pour {vessel_name}', 'body' => 'Le navire {vessel_name} ({registration_number}).']);

        $this->get(route('communications.directory.compose', ['contacts' => [$contact->id], 'template' => $template->id, 'vessel' => $vessel->id]))
            ->assertOk()
            ->assertViewHas('subject', 'Avis pour NAVIRE AMREP')
            ->assertViewHas('body', "Le navire NAVIRE AMREP ({$vessel->registration_number}).")
            ->assertSee($contact->organization);

        $this->get(route('communications.directory.compose'))->assertSessionHasErrors('contacts');
    }

    public function test_a_customized_message_goes_to_each_selected_contact(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $contacts = Contact::factory()->count(2)->create();

        $this->post(route('communications.directory.send'), $this->payload($contacts))->assertRedirect(route('communications.messages.index'))->assertSessionHas('status');

        $messages = SentMessage::orderBy('id')->get();
        $this->assertCount(2, $messages);
        $this->assertSame($contacts->pluck('email')->sort()->values()->all(), $messages->pluck('recipient')->sort()->values()->all());
        $this->assertSame([MessageStatus::Sent], $messages->pluck('status')->unique()->all());
        $this->assertSame('Texte modifié librement pour tous les contacts.', $messages->first()->body);
        $this->assertSame($agent->id, $messages->first()->user_id);
        $this->assertCount(2, $this->sentEmails());
    }

    public function test_generated_documents_are_attached_to_every_message_and_to_the_emails(): void
    {
        $this->signInAs(Role::Director);
        $contacts = Contact::factory()->count(2)->create();
        $vessel = Vessel::factory()->create(['name' => 'NAVIRE PIECES']);
        $day = LogbookDay::factory()->create();
        $observation = Observation::factory()->create(['vessel_id' => $vessel->id]);

        $this->post(route('communications.directory.send'), $this->payload($contacts, [
            'vessel_id' => $vessel->id,
            'documents' => [GeneratedDocuments::VESSEL_SHEET, GeneratedDocuments::LOGBOOK_DAY, GeneratedDocuments::OBSERVATION],
            'logbook_day_id' => $day->id,
            'observation_id' => $observation->id,
        ]))->assertRedirect(route('communications.messages.index'));

        $this->assertSame(3, Document::count());

        foreach (SentMessage::all() as $message) {
            $names = app(DocumentStore::class)->for($message)->pluck('name')->sort()->values()->all();
            $this->assertCount(3, $names);
            $this->assertContains('observation-'.$observation->id.'.pdf', $names);
        }

        foreach ($this->sentEmails() as $email) {
            $this->assertCount(3, $email->getAttachments());
            $this->assertContains('observation-'.$observation->id.'.pdf', collect($email->getAttachments())->map->getFilename()->all());
        }
    }

    public function test_the_message_page_lists_and_serves_its_attachments(): void
    {
        $this->signInAs(Role::Director);
        $contact = Contact::factory()->create();
        $observation = Observation::factory()->create();

        $this->post(route('communications.directory.send'), $this->payload([$contact], ['documents' => [GeneratedDocuments::OBSERVATION], 'observation_id' => $observation->id]));
        $message = SentMessage::sole();
        $document = Document::sole();

        $this->get(route('communications.messages.show', $message))->assertOk()->assertSee($document->name)->assertSee(route('communications.messages.attachment', [$message, $document]), false);
        $this->get(route('communications.messages.attachment', [$message, $document]))->assertOk();

        $other = SentMessage::factory()->create();
        $this->get(route('communications.messages.attachment', [$other, $document]))->assertNotFound();
    }

    public function test_contacts_without_the_needed_channel_data_are_skipped_and_reported(): void
    {
        $this->signInAs(Role::Agent);
        $reachable = Contact::factory()->create(['email' => 'ok@example.test']);
        $noEmail = Contact::factory()->create(['organization' => 'Sans courriel', 'email' => null]);

        $response = $this->post(route('communications.directory.send'), $this->payload([$reachable, $noEmail]))->assertRedirect();

        $this->assertSame(1, SentMessage::count());
        $response->assertSessionHas('error', fn (string $message) => str_contains($message, 'Sans courriel'));
    }

    public function test_whatsapp_needs_a_recorded_consent(): void
    {
        $this->signInAs(Role::Agent);
        $withConsent = Contact::factory()->create(['phone' => '+2250700000011']);
        $withoutConsent = Contact::factory()->create(['phone' => '+2250700000022']);
        app(WhatsAppConsents::class)->grant('+2250700000011');

        $this->post(route('communications.directory.send'), $this->payload([$withConsent, $withoutConsent], ['channel' => 'whatsapp', 'subject' => null]))->assertRedirect();

        $this->assertSame(['+2250700000011'], SentMessage::pluck('recipient')->all());
        $this->assertSame('+2250700000011', app(WhatsAppGateway::class)->sent()[0]['number']);
    }

    public function test_documents_need_their_subject(): void
    {
        $this->signInAs(Role::Agent);
        $contact = Contact::factory()->create();

        $this->post(route('communications.directory.send'), $this->payload([$contact], ['documents' => [GeneratedDocuments::VESSEL_SHEET]]))->assertSessionHasErrors('vessel_id');
        $this->post(route('communications.directory.send'), $this->payload([$contact], ['documents' => [GeneratedDocuments::LOGBOOK_DAY]]))->assertSessionHasErrors('logbook_day_id');
        $this->post(route('communications.directory.send'), $this->payload([$contact], ['documents' => [GeneratedDocuments::OBSERVATION]]))->assertSessionHasErrors('observation_id');
        $this->post(route('communications.directory.send'), $this->payload([$contact], ['documents' => ['passwords']]))->assertSessionHasErrors('documents.0');
        $this->post(route('communications.directory.send'), $this->payload([$contact], ['subject' => '']))->assertSessionHasErrors('subject');

        $this->assertSame(0, SentMessage::count());
    }

    public function test_sending_a_message_about_a_vessel_is_journaled_on_the_vessel(): void
    {
        $this->signInAs(Role::Agent);
        $contact = Contact::factory()->create();
        $vessel = Vessel::factory()->create();

        $this->post(route('communications.directory.send'), $this->payload([$contact], ['vessel_id' => $vessel->id]))->assertRedirect();

        $this->assertSame('message.sent', VesselEvent::sole()->type);
    }

    public function test_sending_needs_the_send_permission(): void
    {
        $contact = Contact::factory()->create();
        $this->signInWithPermissions(Permission::CommunicationsView);

        $this->get(route('communications.directory.index'))->assertOk()->assertDontSee('data-gs-check-all', false);
        $this->get(route('communications.directory.compose', ['contacts' => [$contact->id]]))->assertForbidden();
        $this->post(route('communications.directory.send'), $this->payload([$contact]))->assertForbidden();
        $this->assertSame(0, SentMessage::count());
    }

    public function test_the_amrep_message_templates_are_predefined_in_french_and_english_and_seeding_twice_adds_nothing(): void
    {
        $this->seed(AmrepMessageTemplatesSeeder::class);
        $this->seed(AmrepMessageTemplatesSeeder::class);

        $codes = DB::table('message_templates')->where('code', 'like', 'amrep_%')->where('is_active', true)->pluck('language', 'code');

        $this->assertCount(6, $codes);
        $this->assertSame(3, $codes->filter(fn (string $language) => $language === 'fr')->count());
        $this->assertSame(3, $codes->filter(fn (string $language) => $language === 'en')->count());
    }

    public function test_a_document_cannot_be_generated_for_the_wrong_subject(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(GeneratedDocuments::class)->make(GeneratedDocuments::VESSEL_SHEET, Observation::factory()->create());
    }
}
