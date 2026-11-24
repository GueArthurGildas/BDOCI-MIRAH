<?php

namespace Modules\Operations\Tests\Feature;

use App\Enums\OperationCategoryCode;
use App\Models\Operation;
use App\Models\OperationCategory;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use App\Support\Logbook\LogbookChannels;
use App\Support\Printing\WordRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Operations\Support\OperationDocument;
use Tests\Attributes\Covers;
use Tests\TestCase;
use ZipArchive;

class OperationPrintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(DocumentStore::DISK);
        $this->travelTo(now()->setTime(7, 2));
    }

    private function channel(string $code): int
    {
        return app(LogbookChannels::class)->idFor($code);
    }

    private function openCalipso(): Operation
    {
        $category = OperationCategory::factory()->create(['code' => OperationCategoryCode::Vms]);
        $vessel = Vessel::factory()->create(['name' => 'CALIPSO']);

        $this->post(route('operations.store'), [
            'category_id' => $category->id,
            'name' => 'CALIPSO',
            'summary' => 'Défaut de réception des positions du F/V CALIPSO.',
            'channel_id' => $this->channel('themis'),
            'direction' => 'received',
            'vessel_ids' => [$vessel->id],
            'temperature' => 28,
            'comments' => 'Aucun autre navire impliqué.',
        ])->assertSessionHasNoErrors();

        $operation = Operation::sole();
        $this->travelTo(now()->setTime(7, 20));
        $this->post(route('operations.events.store', $operation), ['channel_id' => $this->channel('phone'), 'direction' => 'sent', 'body' => 'Contact avec l\'armateur du navire.'])->assertSessionHasNoErrors();

        return $operation;
    }

    private function wordXml(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'docx');
        file_put_contents($path, $content);
        $zip = new ZipArchive;
        $zip->open($path);
        $xml = html_entity_decode($zip->getFromName('word/document.xml'), ENT_QUOTES | ENT_XML1);
        $zip->close();
        unlink($path);

        return $xml;
    }

    #[Covers('UC11', partial: true)]
    public function test_the_word_document_follows_the_operation_logbook_template(): void
    {
        $this->signInAs(Role::Agent);
        $operation = $this->openCalipso();

        $response = $this->get(route('operations.word', $operation))->assertOk()->assertHeader('Content-Type', WordRenderer::MIME_TYPE);
        $this->assertStringContainsString(OperationDocument::basename($operation).'.docx', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('Main_courante_OPS_001_VMS_', OperationDocument::basename($operation));

        $xml = $this->wordXml($response->getContent());
        $positions = array_map(fn (string $needle) => mb_strpos($xml, $needle), [
            'Information de contexte',
            'Météo sur la zone de l\'événement',
            'Navire impliqué',
            'Armateur ou propriétaire',
            'Capitaine du navire',
            'Personnel du CSP',
            'MAIN COURANTE DE L\'OPERATION',
            'Défaut de réception des positions du F/V CALIPSO.',
            'Contact avec l\'armateur du navire.',
            'Clôture de l\'opération',
            'Liste des pièces jointes au dossier opération',
        ]);
        $this->assertNotContains(false, $positions);
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions);
        $this->assertStringContainsString('Opération '.$operation->code.' ouverte', $xml);
        $this->assertStringContainsString(now()->format('Ymd'), $xml);
        $this->assertStringContainsString('0070C0', $xml);
        $this->assertStringContainsString('ED7D31', $xml);
        $this->assertStringContainsString('70AD47', $xml);
        $this->assertStringContainsString('Aucun autre navire impliqué.', $xml);
    }

    #[Covers('UC11', partial: true)]
    public function test_closing_files_a_frozen_pdf_of_the_operation_logbook_in_the_operation_file(): void
    {
        $this->signInAs(Role::Agent);
        $operation = $this->openCalipso();

        $this->travelTo(now()->addDays(2)->setTime(10, 21));
        $this->post(route('operations.close', $operation), ['result' => 'VMS réparé.', 'channel_id' => $this->channel('email'), 'direction' => 'received'])->assertSessionHasNoErrors();

        $documents = app(DocumentStore::class)->for($operation->fresh());
        $pdf = $documents->firstWhere('name', OperationDocument::basename($operation).'.pdf');
        $this->assertNotNull($pdf);
        $this->assertCount(2, $documents);
        $this->assertTrue($documents->contains(fn ($document) => str_starts_with($document->name, 'fiche-')));
        $this->assertTrue($pdf->is_frozen);
        $this->assertStringStartsWith('%PDF', Storage::disk(DocumentStore::DISK)->get($pdf->path));

        $xml = $this->wordXml($this->get(route('operations.word', $operation))->assertOk()->getContent());
        $this->assertStringContainsString('Opération '.$operation->code.' close', $xml);
        $this->assertStringContainsString('VMS réparé.', $xml);
        $this->assertStringContainsString('2 j 3 h 19 min', $xml);
        $this->assertStringNotContainsString(OperationDocument::basename($operation).'.pdf', $xml);
    }

    public function test_the_operation_can_be_printed_as_pdf(): void
    {
        $this->signInAs(Role::Agent);
        $operation = $this->openCalipso();

        $response = $this->get(route('operations.pdf', $operation))->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
