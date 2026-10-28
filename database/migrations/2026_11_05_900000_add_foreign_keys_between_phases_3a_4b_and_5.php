<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const KEYS = [
        ['sent_messages', 'operation_id', 'operations'],
        ['sent_messages', 'logbook_entry_id', 'logbook_entries'],
        ['internal_notes', 'logbook_entry_id', 'logbook_entries'],
        ['watch_entries', 'entry_exit_alert_id', 'entry_exit_alerts'],
    ];

    public function up(): void
    {
        foreach (self::KEYS as [$table, $column, $target]) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->foreign($column)->references('id')->on($target));
        }
    }

    public function down(): void
    {
        foreach (self::KEYS as [$table, $column]) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropForeign([$column]));
        }
    }
};
