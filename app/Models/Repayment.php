<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\RepaymentStatus;
use Illuminate\Database\Eloquent\Relations\belongsToMany;

class Repayment extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => RepaymentStatus::class,
    ];
    
    protected $guarded = ['id'];

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Repayment::class);
    }
}
