<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => LoanStatus::class,
        'started_at' => 'datetime',
    ];

    protected $guarded = ['id'];

    public function getRemainingAmountAttribute()
    {
        if ($this->status !== LoanStatus::APPROVED) {
            return null;
        }

        return $this->repayments->sum('amount') - $this->repayments->sum('paid_amount');
    }

    public function getPaidAmountAttribute()
    {
        if ($this->status !== LoanStatus::APPROVED) {
            return null;
        }

        return $this->repayments->sum('paid_amount');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class);
    }
}
