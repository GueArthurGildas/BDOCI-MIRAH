<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Profile\Http\Requests\AvatarRequest;
use Modules\Profile\Support\PersonalSpace;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load(['team', 'roles']);

        return view('profile::show', [
            'user' => $user,
            'roles' => $user->roles->map(fn ($role) => Role::labelFor($role->name))->all(),
            'space' => PersonalSpace::for($user),
        ]);
    }

    public function updateAvatar(AvatarRequest $request, DocumentStore $documents): RedirectResponse
    {
        $document = $documents->store($request->file('avatar'), $request->user());
        $request->user()->update(['avatar_document_id' => $document->id]);

        return to_route('profile.show')->with('status', __('profile::profile.photo.updated'));
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $request->user()->update(['avatar_document_id' => null]);

        return to_route('profile.show')->with('status', __('profile::profile.photo.removed'));
    }

    public function avatar(User $user): StreamedResponse
    {
        $document = $user->avatar_document_id ? Document::find($user->avatar_document_id) : null;

        abort_if($document === null || ! Storage::disk(DocumentStore::DISK)->exists($document->path), 404);

        return Storage::disk(DocumentStore::DISK)->response($document->path, $document->name, [
            'Cache-Control' => 'private, max-age=3600',
            'Content-Type' => $document->mime_type,
        ]);
    }
}
