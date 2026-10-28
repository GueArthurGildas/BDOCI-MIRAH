<?php

namespace Modules\Licenses\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuthorizedListPublication;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\DocumentStore;
use App\Support\Licenses\AuthorizedList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Licenses\Actions\PublishAuthorizedList;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuthorizedListController extends Controller
{
    public function index(AuthorizedList $list): View
    {
        return view('licenses::authorized.index', [
            'rows' => $list->rows(),
            'lastPublication' => $list->lastPublication(),
            'isStale' => $list->isStale(),
            'publications' => AuthorizedListPublication::query()->orderByDesc('version')->limit(20)->get(),
        ]);
    }

    public function publish(Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(PublishAuthorizedList::class, $request->user());

        return to_route('licenses.authorized.index')->with('status', __('licenses::authorized.published'));
    }

    public function download(AuthorizedListPublication $publication): StreamedResponse
    {
        abort_if($publication->document === null, 404);

        return Storage::disk(DocumentStore::DISK)->download($publication->document->path, $publication->document->name);
    }
}
