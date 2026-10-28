<?php

namespace Database\Seeders;

use App\Enums\ConnectorCommentKind;
use App\Enums\RejectionStatus;
use App\Models\Comment;
use App\Models\DataSource;
use App\Models\DataSourceError;
use App\Models\DataSourceRejection;
use App\Models\DataSourceSync;
use App\Models\User;
use App\Support\Authorization\Role;
use Illuminate\Database\Seeder;

class ConnectorActivitySeeder extends Seeder
{
    private const OPEN_ISSUES = [
        'vms-themis' => ['errors' => 0, 'rejections' => 0],
        'e-inspection' => ['errors' => 0, 'rejections' => 3],
        'missions' => ['errors' => 2, 'rejections' => 0],
        'supervision-reports' => ['errors' => 0, 'rejections' => 1],
        'foreign-vessels' => ['errors' => 5, 'rejections' => 12],
    ];

    private const SYNCS_PER_SOURCE = 6;

    public function run(): void
    {
        $adminId = User::role(Role::Admin->value)->value('id');

        foreach (self::OPEN_ISSUES as $key => $open) {
            $source = DataSource::where('key', $key)->firstOrFail();

            $this->seedSyncs($source, $open['errors'] > 0);

            DataSourceError::factory()->count($open['errors'])->for($source)->create();
            DataSourceError::factory()->resolved()->count(2)->for($source)->create(['resolved_by' => $adminId]);
            DataSourceRejection::factory()->count($open['rejections'])->for($source)->create();
            DataSourceRejection::factory()->handled(RejectionStatus::Corrected)->count(2)->for($source)->create(['handled_by' => $adminId]);
        }

        $this->seedComments();
    }

    private function seedSyncs(DataSource $source, bool $failing): void
    {
        foreach (range(0, self::SYNCS_PER_SOURCE - 1) as $index) {
            $startedAt = $source->last_synced_at->copy()->subHours($index * 6);
            $failed = $failing && $index < 3;

            DataSourceSync::factory()->for($source)->create([
                'started_at' => $startedAt,
                'finished_at' => $failed ? null : $startedAt->copy()->addSeconds(40),
                'received_count' => $failed ? 0 : 120 + $index * 7,
                'message' => $failed ? 'Synchronization aborted before completion' : null,
            ]);
        }
    }

    private function seedComments(): void
    {
        $admin = User::role(Role::Admin->value)->first();

        if ($admin === null) {
            return;
        }

        $comments = [
            ['missions', ConnectorCommentKind::Note, 'Délais dépassés intermittents après 20 h. À surveiller avant d\'ouvrir un ticket chez le fournisseur.'],
            ['foreign-vessels', ConnectorCommentKind::Status, 'Source injoignable depuis quatre jours. Le fournisseur a été contacté, en attente de sa réponse.'],
            ['foreign-vessels', ConnectorCommentKind::Action, 'Nouveaux identifiants demandés. Relancer l\'import nocturne dès leur réception, puis réexaminer les enregistrements rejetés.'],
        ];

        foreach ($comments as [$key, $kind, $body]) {
            Comment::factory()->create([
                'object_type' => (new DataSource)->getMorphClass(),
                'object_id' => DataSource::where('key', $key)->value('id'),
                'user_id' => $admin->id,
                'kind' => $kind->value,
                'body' => $body,
            ]);
        }
    }
}
