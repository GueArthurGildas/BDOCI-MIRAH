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
    use TranslatesAttributes {
        attributes as translatedAttributes;
    }

    public const MAX_RECIPIENTS = 50;

    protected function prepareForValidation(): void
    {
        if (! $this->has('recipients') && $this->filled('recipient')) {
            $this->merge(['recipients' => [$this->input('recipient')]]);
        }
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(NoteType::class)],
            'vessel_id' => ['nullable', Rule::exists('vessels', 'id')->whereNull('deleted_at')],
            'urgency' => ['required', Rule::enum(NoteUrgency::class)],
            'recipients' => ['required', 'array', 'min:1', 'max:'.self::MAX_RECIPIENTS],
            'recipients.*' => ['required', 'regex:/^(user|team):\d+$/', 'distinct'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'free_text' => ['nullable', 'string', 'max:2000'],
            'relay' => ['nullable', Rule::enum(NoteRelay::class)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [...$this->translatedAttributes(), 'recipients.*' => __('communications::fields.recipient')];
    }

    public function after(): array
    {
        return [function ($validator) {
            if ($validator->errors()->has('recipients') || $validator->errors()->has('recipients.*')) {
                return;
            }

            foreach ($this->input('recipients') as $recipient) {
                [$kind, $id] = explode(':', $recipient);

                if (! DB::table($kind === 'user' ? 'users' : 'teams')->where('id', $id)->exists()) {
                    $validator->errors()->add('recipients', __('validation.exists', ['attribute' => __('communications::fields.recipient')]));

                    return;
                }
            }
        }];
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->payloads()[0];
    }

    /** @return array<int, array<string, mixed>> one payload per recipient */
    public function payloads(): array
    {
        $common = collect($this->validated())->except(['recipients', 'recipient', 'instruction'])->all();

        return collect($this->validated('recipients'))->map(function (string $recipient) use ($common) {
            [$kind, $id] = explode(':', $recipient);

            return [
                ...$common,
                'recipient_user_id' => $kind === 'user' ? (int) $id : null,
                'recipient_team_id' => $kind === 'team' ? (int) $id : null,
            ];
        })->all();
    }
}
