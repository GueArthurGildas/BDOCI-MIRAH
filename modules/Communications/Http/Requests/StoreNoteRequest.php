<?php

namespace Modules\Communications\Http\Requests;

use App\Enums\NoteRelay;
use App\Enums\NoteType;
use App\Enums\NoteUrgency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Communications\Http\Requests\Concerns\TranslatesAttributes;

class StoreNoteRequest extends FormRequest
{
    use TranslatesAttributes;

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(NoteType::class)],
            'vessel_id' => ['nullable', Rule::exists('vessels', 'id')->whereNull('deleted_at')],
            'urgency' => ['required', Rule::enum(NoteUrgency::class)],
            'recipient' => ['required', 'regex:/^(user|team):\d+$/'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'free_text' => ['nullable', 'string', 'max:2000'],
            'relay' => ['nullable', Rule::enum(NoteRelay::class)],
        ];
    }

    public function after(): array
    {
        return [function ($validator) {
            if ($validator->errors()->has('recipient')) {
                return;
            }

            [$kind, $id] = explode(':', $this->input('recipient'));
            $table = $kind === 'user' ? 'users' : 'teams';

            if (! DB::table($table)->where('id', $id)->exists()) {
                $validator->errors()->add('recipient', __('validation.exists', ['attribute' => __('communications::fields.recipient')]));
            }
        }];
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        [$kind, $id] = explode(':', $this->validated('recipient'));

        return [
            ...collect($this->validated())->except(['recipient', 'instruction'])->all(),
            'recipient_user_id' => $kind === 'user' ? (int) $id : null,
            'recipient_team_id' => $kind === 'team' ? (int) $id : null,
        ];
    }
}
