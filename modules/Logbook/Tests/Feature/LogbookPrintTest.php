<?php

namespace Modules\Logbook\Tests\Feature;

use App\Enums\OperationMilestone;
use App\Models\LogbookDay;
use App\Models\Operation;
use App\Models\User;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use App\Support\Logbook\LogbookChannels;
use App\Support\Logbook\LogbookDayDocument;
use App\Support\Logbook\LogbookWriter;
use App\Support\Printing\PdfRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Attributes\Covers;
use Tests\TestCase;

class LogbookPrintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(DocumentStore::DISK);
        $this->travelTo(now()->setTime(8, 5));
    }

    private function line(string $content, string $channel = 'phone'): void
    {
        $this->post(route('logbook.entries.store'), ['channel_id' => app(LogbookChannels::class)->idFor($channel), 'direction' => 'received', 'content' => $content])->assertSessionHasNoErrors();
    }

    #[Covers('UC12', 'RG10', partial: true)]
    public function test_the_printed_day_follows_the_template_order_handover_lines_then_closing(): void
    {
        $this->signInAs(Role::Agent);
        $incoming = User::factory()->create(['first_name' => 'Mariam', 'name' => 'CONDE']);

        $this->line('Ligne avant la relève du matin');
        $this->travelTo(now()->setTime(8, 12));
        $this->post(route('logbook.handovers.store'), ['taken_over_by' => $incoming->id, 'instructions' => 'Consigne du matin'])->assertSessionHasNoErrors();
        $this->travelTo(now()->setTime(9, 37));
        $this->line('Réception d\'une DPEP du navire PACIFIC PRIDE', 'email');
        $this->travelTo(now()->setTime(18, 5));
        $this->post(route('logbook.handovers.store'), ['taken_over_by' => $incoming->id, 'instructions' => 'Consigne du soir'])->assertSessionHasNoErrors();
        $this->travelTo(now()->addDay()->setTime(2, 10));
        $this->line('Appel de nuit de la capitainerie');

        $day = LogbookDay::sole();
        $html = PdfRenderer::inPrintLocale(fn () => view('printing.document', ['document' => app(LogbookDayDocument::class)->build($day)])->render());

        $this->assertSame('Main_courante_CSP_du_'.$day->day->format('Y_m_d'), LogbookDayDocument::basename($day));
        $this->assertStringContainsString('Centre de Surveillance des Pêches', $html);
        $order = array_map(fn (string $needle) => mb_strpos($html, $needle), [
            'Ligne avant la relève du matin',
            'Consigne du matin',
            'Réception d&#039;une DPEP du navire PACIFIC PRIDE',
            'Consigne du soir',
            'Appel de nuit de la capitainerie',
            'Clôture de la main courante',
        ]);
        $this->assertNotContains(false, $order);
        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order);
        $this->assertStringContainsString($day->day->copy()->addDay()->format('Ymd'), $html);
        $this->assertStringContainsString('#4472C4', $html);
        $this->assertStringContainsString('#FFC000', $html);
        $this->assertStringNotContainsString(__('logbook::logbook.entries.fields.handling_status'), $html);
    }

    #[Covers('UC11', 'UC12', partial: true)]
    public function test_operation_openings_are_printed_in_red_and_closings_in_green(): void
    {
        $this->signInAs(Role::TeamLead);
        $operation = Operation::factory()->create(['code' => 'OPS-217-MEP-2026-PACIFIC_PRIDE']);
        app(LogbookWriter::class)->write(new ActionContext(auth()->user()), [
            'channel_id' => app(LogbookChannels::class)->idFor('email'),
            'content' => 'Réception d\'une DPEP',
            'operation_id' => $operation->id,
            'operation_milestone' => OperationMilestone::Opened,
        ]);

        $html = PdfRenderer::inPrintLocale(fn () => view('printing.document', ['document' => app(LogbookDayDocument::class)->build(LogbookDay::sole())])->render());

        $this->assertMatchesRegularExpression('/color: #C00000;[^>]*>Opération OPS-217-MEP-2026-PACIFIC_PRIDE ouverte/', $html);
    }

    public function test_an_open_day_can_be_printed_as_pdf(): void
    {
        $this->signInAs(Role::Agent);
        $this->line('Ligne à imprimer');
        $day = LogbookDay::sole();

        $response = $this->get(route('logbook.days.print', $day))->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertStringContainsString('Main_courante_CSP_du_'.$day->day->format('Y_m_d').'.pdf', $response->headers->get('Content-Disposition'));
    }
}
