<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\LoanStatus;
use App\Enums\RepaymentStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Money\Money;

class Loan extends Model
{
    use HasFactory;

    protected $casts = [
        'status'     => LoanStatus::class,
        'started_at' => 'datetime',
    ];

    protected $guarded = ['id'];

    public function getRemainingAmountAttribute()
    {
        if ($this->status !== LoanStatus::APPROVED) {
            return $this->amount;
        }

        return Money::USD( (int) $this->repayments->sum('amount') * 100 )
            ->subtract(Money::USD( (int) $this->repayments->sum('paid_amount') * 100 ))
            ->getAmount()/100;
    }

    public function getPaidAmountAttribute()
    {
        if ($this->status !== LoanStatus::APPROVED) {
            return 0;
        }

        return Money::USD( (int) $this->repayments->sum('paid_amount') * 100 )->getAmount()/100;
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->remaining_amount == $this->amount) {
            return RepaymentStatus::UNPAID;
        }

        return $this->remaining_amount === 0 ? RepaymentStatus::PAID : RepaymentStatus::PARTIAL_PAID;
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class);
    }
}
