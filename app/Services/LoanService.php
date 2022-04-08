<?php

namespace App\Services;

use App\Models\User;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\Transaction;
use App\Models\RepaymentTransaction;
use App\Exceptions\AspireException;
use Auth;
use App\Enums\LoanStatus;
use App\Enums\RepaymentStatus;
use Illuminate\Database\Eloquent\Collection;
use Money\Money;

class LoanService
{
    public function getAllByUser(User $user): Collection
    {
        return $user->loans;
    }
    
    public function store(array $data): Loan
    {
        try {
            $loan = Loan::create([
                'user_id' => Auth::id(),
                'term'    => $data['term'],
                'amount'  => $data['amount'],
                'status'  => LoanStatus::PENDING,
            ]);
        } catch (Exception $e) {
            throw new AspireException($e);
        }

        return $loan;
    }

    public function approve(Loan $loan): Loan
    {
        $loan->status     = LoanStatus::APPROVED;
        $loan->started_at = now();
        $loan->save();

        $amounts = Money::USD( (int) $loan->amount * 100 );

        $amounts = $amounts->allocateTo($loan->term);
        $amounts = array_map(function ($amount) { return $amount->getAmount()/100; }, $amounts);
        $amounts = array_reverse($amounts);
        $amounts = collect($amounts)->map(function ($amount, $index) use ($loan) {
                return [
                    'amount'       => $amount,
                    'paid_amount'  => 0,
                    'status'       => RepaymentStatus::UNPAID,
                    'scheduled_at' => $loan->started_at->addDays(($index + 1) * 7),
                ];
            });

        $loan->repayments()->createMany($amounts->toArray());

        $loan->load('repayments');

        return $loan;
    }

    public function getDetail(Loan|int $loan): Loan
    {
        if (is_integer($loan)) {
            $loan = Loan::find($loan);
        }

        $loan->load('repayments.transactions');

        return $loan;
    }

    public function getNextScheduledRepayment(Loan $loan): ?Repayment
    {
        return $loan->repayments()
            ->whereIn('status', [RepaymentStatus::UNPAID, RepaymentStatus::PARTIAL_PAID])
            ->orderBy('scheduled_at')
            ->first();
    }

    public function isRepaymentMissed(Loan $loan): bool
    {
        $nextRepayment = $this->getNextScheduledRepayment($loan);

        if (empty($nextRepayment)) {
            return false;
        }

        return $nextRepayment->scheduled_at->lt(now());
    }

    public function initRepay(Loan $loan, $amount): Collection
    {
        $transaction = Transaction::create([
            'amount' => $amount
        ]);

        $this->repay($loan, $transaction, $transaction->amount);

        return $transaction->repayments()->get();
    }

    public function repay(Loan $loan, Transaction $transaction, $amount): void
    {
        $nextRepayment = $this->getNextScheduledRepayment($loan);
        $extraAmount   = $amount + $nextRepayment->paid_amount - $nextRepayment->amount;
        
        $nextRepayment->paid_amount = $amount >= $nextRepayment->amount - $nextRepayment->paid_amount
            ? $nextRepayment->amount
            : $nextRepayment->paid_amount + $amount;

        $nextRepayment->status      = $nextRepayment->paid_amount === $nextRepayment->amount
            ? RepaymentStatus::PAID
            : RepaymentStatus::PARTIAL_PAID;

        $nextRepayment->paid_at     = $nextRepayment->status === RepaymentStatus::PAID ? now() : null;
        $nextRepayment->save();

        RepaymentTransaction::create([
            'repayment_id'   => $nextRepayment->id,
            'transaction_id' => $transaction->id,
        ]);

        if ($extraAmount <= 0) {
            return;
        }
        $this->repay($loan, $transaction, $extraAmount);
    }
}
