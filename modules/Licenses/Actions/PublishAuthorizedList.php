<?php

namespace Modules\Licenses\Actions;

use App\Models\AuthorizedListPublication;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use App\Support\Licenses\AuthorizedList;
use Modules\Licenses\Support\PdfRenderer;

class PublishAuthorizedList extends Action
{
    public function __construct(
        private readonly AuthorizedList $list,
        private readonly DocumentStore $documents,
        private readonly PdfRenderer $pdf,
    ) {}

    public function code(): string
    {
        return 'licenses.publish_authorized_list';
    }

    public function permission(): Permission
    {
        return Permission::LicensesPublish;
    }

    public function handle(ActionContext $context, array $input): AuthorizedListPublication
    {
        $version = (int) AuthorizedListPublication::max('version') + 1;
        $publishedAt = now();

        $document = $this->documents->storeContent(
            "Liste_navires_autorises_v{$version}.pdf",
            $this->pdf->render('licenses::pdf.authorized-list', ['rows' => $this->list->rows(), 'version' => $version, 'publishedAt' => $publishedAt]),
            'application/pdf',
            $context->actor,
            freeze: true,
        );

        return AuthorizedListPublication::create(['version' => $version, 'document_id' => $document->id, 'published_at' => $publishedAt]);
    }
}
