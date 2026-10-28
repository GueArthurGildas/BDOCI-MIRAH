<?php

namespace Modules\Licenses\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LicenseRegister;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\DocumentStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Licenses\Actions\GenerateLicenseRegister;
use Modules\Licenses\Http\Requests\RegisterYearRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegisterController extends Controller
{
    public function index(): View
    {
        return view('licenses::register.index', [
            'registers' => LicenseRegister::query()->orderByDesc('year')->get(),
            'currentYear' => now()->year,
        ]);
    }

    public function generate(RegisterYearRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(GenerateLicenseRegister::class, $request->user(), ['year' => $request->validated('year')]);

        return to_route('licenses.register.index')->with('status', __('licenses::register.generated'));
    }

    public function download(int $year): StreamedResponse
    {
        $register = LicenseRegister::with('document')->findOrFail($year);
        abort_if($register->document === null, 404);

        return Storage::disk(DocumentStore::DISK)->download($register->document->path, $register->document->name);
    }
}
