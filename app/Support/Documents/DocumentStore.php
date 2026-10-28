<?php

namespace App\Support\Documents;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentStore
{
    public const DISK = 'local';

    public function store(UploadedFile $file, ?User $filedBy = null, bool $freeze = false): Document
    {
        $path = $file->storeAs('documents/'.now()->format('Y/m'), Str::uuid().'.'.$file->getClientOriginalExtension(), self::DISK);

        return Document::create([
            'name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'path' => $path,
            'sha256_fingerprint' => hash_file('sha256', $file->getRealPath()),
            'is_frozen' => $freeze,
            'filed_by' => $filedBy?->id,
        ]);
    }

    public function storeContent(string $name, string $content, string $mimeType, ?User $filedBy = null, bool $freeze = false): Document
    {
        $path = 'documents/'.now()->format('Y/m').'/'.Str::uuid().'.'.pathinfo($name, PATHINFO_EXTENSION);
        Storage::disk(self::DISK)->put($path, $content);

        return Document::create([
            'name' => $name,
            'mime_type' => $mimeType,
            'path' => $path,
            'sha256_fingerprint' => hash('sha256', $content),
            'is_frozen' => $freeze,
            'filed_by' => $filedBy?->id,
        ]);
    }

    public function attach(Document $document, Model $object): void
    {
        DB::table('document_links')->insertOrIgnore([
            'document_id' => $document->id,
            'object_type' => $object->getMorphClass(),
            'object_id' => $object->getKey(),
        ]);
    }

    public function freeze(Document $document): void
    {
        $document->update(['is_frozen' => true]);
    }

    public function isIntact(Document $document): bool
    {
        $disk = Storage::disk(self::DISK);

        return $document->sha256_fingerprint !== null
            && $disk->exists($document->path)
            && hash_equals($document->sha256_fingerprint, hash('sha256', $disk->get($document->path)));
    }

    /** @return Collection<int, Document> */
    public function for(Model $object): Collection
    {
        return Document::query()
            ->whereIn('id', DB::table('document_links')
                ->where('object_type', $object->getMorphClass())
                ->where('object_id', $object->getKey())
                ->select('document_id'))
            ->latest('id')
            ->get();
    }
}
