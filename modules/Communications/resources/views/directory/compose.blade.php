<x-layouts.app :title="__('communications::directory.compose.title')">
    <x-ui.page-header :title="__('communications::directory.compose.title')" :subtitle="__('communications::directory.compose.subtitle')" :breadcrumbs="[__('communications::directory.title') => route('communications.directory.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-4">
                <x-ui.card :title="__('communications::directory.recipients')" :subtitle="trans_choice('communications::directory.recipients_count', $contacts->count(), ['count' => $contacts->count()])" flush>
                    <x-ui.table :paginate="false">
                        <x-slot:head>
                            <th>{{ __('communications::directory.columns.organization') }}</th>
                            <th>{{ __('communications::directory.compose.reachable') }}</th>
                        </x-slot:head>
                        @foreach ($contacts as $contact)
                            <tr>
                                <td class="text-wrap">{{ $contact->organization ?: '—' }}<div class="text-muted f-12">{{ $contact->country?->label }}</div></td>
                                <td>
                                    <span @class(['label', 'label-success' => $contact->email, 'label-default' => ! $contact->email]) title="{{ $contact->email }}"><i class="feather icon-mail"></i></span>
                                    <span @class(['label', 'label-success' => $contact->phone, 'label-default' => ! $contact->phone]) title="{{ $contact->phone }}"><i class="feather icon-phone"></i></span>
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                </x-ui.card>
            </div>

            <div class="col-xl-8">
                <x-ui.card :title="__('communications::directory.compose.prefill_title')" :subtitle="__('communications::directory.compose.prefill_hint')">
                    <form method="GET" action="{{ route('communications.directory.compose') }}" class="row g-3 align-items-end">
                        @foreach ($contacts as $contact)
                            <input type="hidden" name="contacts[]" value="{{ $contact->id }}">
                        @endforeach
                        <x-ui.filter-select name="template" :label="__('communications::directory.compose.template')" :options="$templates" :value="$template?->id" class="col-md-5" />
                        <x-ui.filter-select name="vessel" :label="__('communications::directory.compose.vessel')" :options="$vessels" :value="$vessel?->id" class="col-md-5" />
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-default btn-sm"><i class="feather icon-refresh-cw"></i> {{ __('communications::directory.compose.prefill') }}</button>
                        </div>
                    </form>
                </x-ui.card>

                <x-ui.card :title="__('communications::directory.compose.message_title')">
                    <form data-confirm="{{ __('communications::directory.compose.confirm', ['count' => $contacts->count()]) }}" data-confirm-label="{{ __('communications::directory.compose.confirm_yes') }}" method="POST" action="{{ route('communications.directory.send') }}">
                        @csrf
                        @foreach ($contacts as $contact)
                            <input type="hidden" name="contacts[]" value="{{ $contact->id }}">
                        @endforeach
                        @if ($template)
                            <input type="hidden" name="message_template_id" value="{{ $template->id }}">
                        @endif
                        @if ($vessel)
                            <input type="hidden" name="vessel_id" value="{{ $vessel->id }}">
                        @endif

                        @include('communications::partials.choice-buttons', ['name' => 'channel', 'label' => __('communications::fields.channel'), 'options' => $channels, 'selected' => $channel, 'required' => true])
                        <x-ui.form.input name="subject" :label="__('communications::fields.subject')" :value="$subject" maxlength="190" />
                        <x-ui.form.textarea name="body" :label="__('communications::fields.body')" :value="$body" rows="9" required />
                        <p class="text-muted f-12 offset-sm-3">{{ __('communications::directory.compose.body_hint') }}</p>

                        <div class="mb-3 row">
                            <label class="form-label col-sm-3 col-form-label">{{ __('communications::directory.documents.title') }}</label>
                            <div class="col-sm-9">
                                <div class="checkbox-fade fade-in-primary">
                                    <label class="form-label">
                                        <input type="checkbox" name="documents[]" value="vessel_sheet" @checked(in_array('vessel_sheet', old('documents', []), true)) @disabled(! $vessel)>
                                        <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                        <span>{{ __('communications::directory.documents.vessel_sheet') }}@unless ($vessel) <small class="text-muted">({{ __('communications::directory.documents.needs_vessel') }})</small>@endunless</span>
                                    </label>
                                </div>
                                <div class="checkbox-fade fade-in-primary d-block">
                                    <label class="form-label">
                                        <input type="checkbox" name="documents[]" value="logbook_day" @checked(in_array('logbook_day', old('documents', []), true))>
                                        <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                        <span>{{ __('communications::directory.documents.logbook_day') }}</span>
                                    </label>
                                </div>
                                <select name="logbook_day_id" class="form-select form-select-sm m-b-10" data-placeholder="{{ __('communications::directory.documents.logbook_day_subject') }}">
                                    <option value="">{{ __('communications::directory.documents.logbook_day_subject') }}</option>
                                    @foreach ($days as $id => $label)
                                        <option value="{{ $id }}" @selected((string) old('logbook_day_id') === (string) $id)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="checkbox-fade fade-in-primary d-block">
                                    <label class="form-label">
                                        <input type="checkbox" name="documents[]" value="observation" @checked(in_array('observation', old('documents', []), true))>
                                        <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                        <span>{{ __('communications::directory.documents.observation') }}</span>
                                    </label>
                                </div>
                                <select name="observation_id" class="form-select form-select-sm" data-placeholder="{{ __('communications::directory.documents.observation_subject') }}">
                                    <option value="">{{ __('communications::directory.documents.observation_subject') }}</option>
                                    @foreach ($observations as $id => $label)
                                        <option value="{{ $id }}" @selected((string) old('observation_id') === (string) $id)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">{{ __('communications::directory.documents.hint') }}</div>
                                @foreach (['documents', 'documents.*', 'logbook_day_id', 'observation_id', 'vessel_id', 'contacts', 'contacts.*'] as $field)
                                    @error($field)
                                        <div class="text-danger f-12">{{ $message }}</div>
                                    @enderror
                                @endforeach
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary"><i class="feather icon-send"></i> {{ __('communications::directory.compose.send') }}</button>
                                <a href="{{ route('communications.directory.index') }}" class="btn btn-default m-l-10">{{ __('ui.cancel') }}</a>
                            </div>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
