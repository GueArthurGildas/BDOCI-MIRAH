<?php

namespace Modules\Licenses\Actions;

use App\Models\License;
use App\Models\LicenseRegister;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use Modules\Licenses\Support\PdfRenderer;

class GenerateLicenseRegister extends Action
{
    public function __construct(private readonly DocumentStore $documents, private readonly PdfRenderer $pdf) {}

    public function code(): string
    {
        return 'licenses.generate_register';
    }

    public function permission(): Permission
    {
        return Permission::LicensesPublish;
    }

    public function handle(ActionContext $context, array $input): LicenseRegister
    {
        $year = (int) $input['year'];

        $licenses = License::query()
            ->with(['vessel', 'zones', 'gears'])
            ->whereDate('expires_on', '>=', "{$year}-01-01")
            ->where(fn ($query) => $query->whereNull('issued_on')->orWhereDate('issued_on', '<=', "{$year}-12-31"))
            ->orderBy('number')
            ->get();

        $document = $this->documents->storeContent(
            "Registre_licences_{$year}.pdf",
            $this->pdf->render('licenses::pdf.register', ['licenses' => $licenses, 'year' => $year]),
            'application/pdf',
            $context->actor,
        );

        return LicenseRegister::updateOrCreate(['year' => $year], ['document_id' => $document->id, 'generated_at' => now()]);
    }
}
