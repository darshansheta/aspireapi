<?php

namespace App\Services;

use App\Models\User;
use App\Models\Loan;
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
            });;

        \Log::info($amounts->keys());

        $loan->repayments()->createMany($amounts->toArray());

        $loan->load('repayments');

        return $loan;
    }
}
