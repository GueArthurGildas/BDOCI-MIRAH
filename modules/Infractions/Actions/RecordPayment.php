<?php

namespace Modules\Infractions\Actions;

use App\Models\Decision;
use App\Models\Payment;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Carbon;

class RecordPayment extends Action
{
    public function code(): string
    {
        return 'infractions.record_payment';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsDecide;
    }

    public function handle(ActionContext $context, array $input): Payment
    {
        $decision = Decision::with('followUp.infractionCase')->findOrFail($input['decision_id']);
        $case = $decision->followUp->infractionCase;

        $payment = $decision->payments()->create([
            'amount' => $input['amount'],
            'currency' => strtoupper($input['currency'] ?? 'XOF'),
            'paid_on' => filled($input['paid_on'] ?? null) ? Carbon::parse($input['paid_on']) : null,
        ]);

        $context->publish(
            vessel: $case->vessel_id,
            module: 'infractions',
            type: 'infraction_case.payment_recorded',
            subject: $payment,
            summaryKey: 'infractions::events.payment_recorded',
            params: ['reference' => $case->reference, 'amount' => (string) $payment->amount, 'currency' => $payment->currency],
        );

        return $payment;
    }
}
