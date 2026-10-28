<x-ui.form.input name="name" :label="__('vessels::vessels.fields.name')" :value="$vessel->name" required />
<x-ui.form.input name="registration_number" :label="__('vessels::vessels.fields.registration_number')" :value="$vessel->registration_number" required />
<x-ui.form.input name="imo_number" :label="__('vessels::vessels.fields.imo_number')" :value="$vessel->imo_number" />
<x-ui.form.input name="call_sign" :label="__('vessels::vessels.fields.call_sign')" :value="$vessel->call_sign" />
<x-ui.form.select name="flag" :label="__('vessels::vessels.fields.flag')" :options="App\Support\Localization\Countries::options()" :value="$vessel->flag ?? 'CI'" required />
<x-ui.form.select name="category" :label="__('vessels::vessels.fields.category')" :options="App\Enums\VesselCategory::options()" :value="$vessel->category" required />
<x-ui.form.select name="type" :label="__('vessels::vessels.fields.type')" :options="App\Enums\VesselType::options()" :value="$vessel->type" required />
<x-ui.form.select name="status" :label="__('vessels::vessels.fields.status')" :options="App\Enums\VesselStatus::options()" :value="$vessel->status" required />
<x-ui.form.input name="owner" :label="__('vessels::vessels.fields.owner')" :value="$vessel->owner" />
<x-ui.form.input name="length_meters" type="number" step="0.01" :label="__('vessels::vessels.fields.length_meters')" :value="$vessel->length_meters" />
<x-ui.form.input name="gross_tonnage" type="number" step="0.01" :label="__('vessels::vessels.fields.gross_tonnage')" :value="$vessel->gross_tonnage" />
<x-ui.form.select name="fleet_segment" :label="__('vessels::vessels.fields.fleet_segment')" :options="App\Enums\FleetSegment::options()" :value="$vessel->fleet_segment" />
<x-ui.form.select name="fleet_type" :label="__('vessels::vessels.fields.fleet_type')" :options="App\Enums\FleetType::options()" :value="$vessel->fleet_type" />
<x-ui.form.select name="legal_status" :label="__('vessels::vessels.fields.legal_status')" :options="App\Enums\LegalStatus::options()" :value="$vessel->legal_status" />
<x-ui.form.select name="vessel_type_id" :label="__('vessels::vessels.fields.vessel_type_id')" :options="App\Models\VesselTypeRecord::orderBy('label')->pluck('label', 'id')" :value="$vessel->vessel_type_id" />
<x-ui.form.select name="home_port_id" :label="__('vessels::vessels.fields.home_port_id')" :options="App\Models\Port::orderBy('name')->pluck('name', 'id')" :value="$vessel->home_port_id" />
<x-ui.form.input name="construction_year" type="number" :label="__('vessels::vessels.fields.construction_year')" :value="$vessel->construction_year" />
<x-ui.form.input name="beam_m" type="number" step="0.01" :label="__('vessels::vessels.fields.beam_m')" :value="$vessel->beam_m" />
<x-ui.form.input name="net_tonnage" type="number" step="0.01" :label="__('vessels::vessels.fields.net_tonnage')" :value="$vessel->net_tonnage" />
<x-ui.form.input name="propulsion" :label="__('vessels::vessels.fields.propulsion')" :value="$vessel->propulsion" />
<x-ui.form.checkbox name="is_tuna_vessel" :label="__('vessels::vessels.fields.is_tuna_vessel')" :checked="$vessel->is_tuna_vessel" />
