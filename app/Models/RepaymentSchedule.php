<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepaymentSchedule extends Model
{
    use HasFactory;

    public const STATUS = [1 => "APPROVED", 2 => "REJECTED", 3 => "PAID", 4 => "LATE"];

    public const STATUS_APPROVED = 1;
    public const STATUS_REJECTED = 2;
    public const STATUS_PAID = 3;
    public const STATUS_LATE = 4;

    public $fillable = [
        'due_date',
        'due_payment',
        'remaining',
        'times_reminded',
        'user_id',
        'weekly_loan_id',
        'status'
    ];

    public $hidden = [
        'id',
        'weekly_loan_id'
    ];

    public function getStatusAttribute($value)
    {
        return $this::STATUS[$value];
    }

    public static function getStatusText($value)
    {
        return RepaymentSchedule::STATUS[$value];
    }

    public function weeklyLoan() {
        return $this->belongsTo(WeeklyLoan::class, 'weekly_loan_id');
    }
}
