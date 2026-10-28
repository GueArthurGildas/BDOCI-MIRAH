<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE zones ADD COLUMN geom geometry(Geometry, 4326)');

        DB::statement(<<<'SQL'
CREATE OR REPLACE FUNCTION zones_sync_geom() RETURNS trigger AS $$
BEGIN
    IF NEW.geometry IS NULL THEN
        NEW.geom := NULL;
    ELSE
        NEW.geom := ST_SetSRID(ST_GeomFromGeoJSON(NEW.geometry::text), 4326);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql
SQL);

        DB::statement('DROP TRIGGER IF EXISTS zones_sync_geom ON zones');
        DB::statement('CREATE TRIGGER zones_sync_geom BEFORE INSERT OR UPDATE OF geometry ON zones FOR EACH ROW EXECUTE FUNCTION zones_sync_geom()');
        DB::statement('UPDATE zones SET geometry = geometry');
        DB::statement('CREATE INDEX zones_geom_gist ON zones USING gist (geom)');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS zones_sync_geom ON zones');
        DB::statement('DROP FUNCTION IF EXISTS zones_sync_geom()');
        DB::statement('DROP INDEX IF EXISTS zones_geom_gist');
        DB::statement('ALTER TABLE zones DROP COLUMN IF EXISTS geom');
    }
};
