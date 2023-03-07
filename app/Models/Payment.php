<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashid;

class Payment extends Model
{
    use HasFactory;
    use HasHashid;

    private const HASHID_PREFIX = 'py_';

    public $fillable = [
        'user_id',
        'weekly_loan_id',
        'amount',
        'hashid'
    ];

    public $appends = [
        'hashid'
    ];

    public $hidden = [
        'id',
        'weekly_loan_id',
        'weeklyLoan'
    ];

    public function weeklyLoan()
    {
        return $this->belongsTo(WeeklyLoan::class, 'weekly_loan_id', 'id');
    }
}
