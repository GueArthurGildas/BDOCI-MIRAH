<?php

namespace Modules\Registry\Support;

use App\Enums\ExternalListType;
use App\Enums\InstitutionType;
use App\Enums\ZoneRuleSegment;
use App\Models\Accreditation;
use App\Models\Agent;
use App\Models\Contact;
use App\Models\Country;
use App\Models\ExternalList;
use App\Models\Gear;
use App\Models\Institution;
use App\Models\LegalEntity;
use App\Models\Person;
use App\Models\Port;
use App\Models\Presentation;
use App\Models\Species;
use App\Models\User;
use App\Models\VesselTypeRecord;
use App\Models\Zone;
use App\Models\ZoneRule;
use Illuminate\Support\Collection;

final class ResourceCatalog
{
    /** @return Collection<string, RegistryResource> */
    public static function all(): Collection
    {
        $countries = fn () => Country::orderBy('label')->pluck('label', 'id')->all();
        $institutions = fn () => Institution::orderBy('name')->pluck('name', 'id')->all();
        $legalEntities = fn () => LegalEntity::orderBy('name')->pluck('name', 'id')->all();
        $zones = fn () => Zone::orderBy('name')->pluck('name', 'id')->all();
        $agents = fn () => Agent::orderBy('name')->pluck('name', 'id')->all();
        $users = fn () => User::orderBy('name')->pluck('name', 'id')->all();

        return collect([
            new RegistryResource('countries', Country::class, 'icon-globe', [
                Field::text('iso3_code', required: true, unique: true, max: 3),
                Field::text('iso2_code', unique: true, max: 2),
                Field::text('label', required: true, max: 100),
            ], ['iso3_code', 'iso2_code', 'label'], 'label'),
            new RegistryResource('institutions', Institution::class, 'icon-briefcase', [
                Field::select('type', fn () => InstitutionType::options(), required: true),
                Field::text('name', required: true, max: 160),
                Field::select('country_id', $countries),
            ], ['name', 'type', 'country_id'], 'name'),
            new RegistryResource('legal-entities', LegalEntity::class, 'icon-home', [
                Field::text('name', required: true),
                Field::text('address', max: 255),
                Field::text('phone', max: 40),
                Field::email('email'),
                Field::select('country_id', $countries),
            ], ['name', 'phone', 'email', 'country_id'], 'name'),
            new RegistryResource('people', Person::class, 'icon-user', [
                Field::text('name', required: true),
                Field::text('first_name', max: 120),
                Field::select('nationality_country_id', $countries),
                Field::text('address', max: 255),
                Field::text('phone', max: 40),
                Field::email('email'),
                Field::select('legal_entity_id', $legalEntities),
            ], ['name', 'first_name', 'nationality_country_id', 'legal_entity_id'], 'name'),
            new RegistryResource('vessel-types', VesselTypeRecord::class, 'icon-layers', [
                Field::text('code', required: true, unique: true, max: 10),
                Field::text('label', required: true, max: 160),
            ], ['code', 'label'], 'code'),
            new RegistryResource('species', Species::class, 'icon-droplet', [
                Field::text('fao_code', required: true, unique: true, max: 10),
                Field::text('name', required: true, max: 160),
                Field::text('scientific_name', max: 190),
            ], ['fao_code', 'name', 'scientific_name'], 'fao_code'),
            new RegistryResource('gears', Gear::class, 'icon-anchor', [
                Field::text('code_fao', required: true, unique: true, max: 10),
                Field::text('label', required: true, max: 160),
            ], ['code_fao', 'label'], 'code_fao'),
            new RegistryResource('presentations', Presentation::class, 'icon-package', [
                Field::text('code', required: true, unique: true, max: 10),
                Field::text('label', required: true, max: 160),
                Field::number('conversion_factor', step: '0.001', rules: ['numeric', 'min:0', 'max:999']),
            ], ['code', 'label', 'conversion_factor'], 'code'),
            new RegistryResource('ports', Port::class, 'icon-map-pin', [
                Field::text('code', required: true, unique: true, max: 20),
                Field::text('name', required: true, unique: true, max: 120),
                Field::text('locality', required: true),
                Field::number('latitude', required: true, step: '0.000001', rules: ['numeric', 'between:-90,90']),
                Field::number('longitude', required: true, step: '0.000001', rules: ['numeric', 'between:-180,180']),
                Field::select('zone_id', $zones),
                Field::checkbox('is_designated'),
            ], ['code', 'name', 'locality', 'is_designated'], 'code'),
            new RegistryResource('zone-rules', ZoneRule::class, 'icon-map', [
                Field::select('zone_id', $zones, required: true),
                Field::select('segment', fn () => ZoneRuleSegment::options(), required: true),
                Field::number('nautical_miles', required: true, rules: ['integer', 'between:1,200']),
            ], ['zone_id', 'segment', 'nautical_miles'], 'zone_id'),
            new RegistryResource('external-lists', ExternalList::class, 'icon-list', [
                Field::select('institution_id', $institutions, required: true),
                Field::select('type', fn () => ExternalListType::options(), required: true),
                Field::text('label', required: true, max: 160),
            ], ['label', 'type', 'institution_id'], 'label'),
            new RegistryResource('contacts', Contact::class, 'icon-phone', [
                Field::text('category', max: 80),
                Field::select('country_id', $countries),
                Field::select('institution_id', $institutions),
                Field::text('organization', max: 160),
                Field::email('email'),
                Field::text('phone', max: 40),
            ], ['organization', 'category', 'country_id', 'email', 'phone'], 'organization'),
            new RegistryResource('agents', Agent::class, 'icon-users', [
                Field::text('name', required: true),
                Field::select('user_id', $users),
                Field::text('grade', max: 80),
                Field::text('agency', max: 120),
                Field::checkbox('is_inspector'),
            ], ['name', 'grade', 'agency', 'is_inspector'], 'name'),
            new RegistryResource('accreditations', Accreditation::class, 'icon-award', [
                Field::select('agent_id', $agents, required: true),
                Field::text('card_number', max: 60),
                Field::text('decision', max: 120),
                Field::date('returned_on'),
                Field::textarea('trainings'),
            ], ['agent_id', 'card_number', 'decision', 'returned_on'], 'id'),
        ])->keyBy(fn (RegistryResource $resource) => $resource->key);
    }

    public static function find(string $key): RegistryResource
    {
        return self::all()->get($key) ?? abort(404);
    }
}
