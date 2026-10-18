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
