<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable;
use App\Traits\HasHashid;

class WeeklyLoan extends Model
{
    use HasFactory;
    use Blameable;
    use HasHashid;

    private const HASHID_PREFIX = 'wl_';

    public const STATUS = [0 => "PENDING", 1 => "APPROVED", 2 => "REJECTED", 3 => "PAID"];

    public const STATUS_PENDING = 0;
    public const STATUS_APPROVED = 1;
    public const STATUS_REJECTED = 2;
    public const STATUS_PAID = 3;

    /**
     * Fillable
     * 
     * @var array
     */
    protected $fillable = [
        'amount',
        'loan_term',
        'approved_rejected_by'
    ];

    protected $appends = [
        'hashid',
        'RepaymentSchedule',
    ];

    protected $hidden = [
        'id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function payment()
    {
        return $this->hasMany(Payment::class, 'id');
    }

    public function RepaymentSchedules()
    {
        return $this->hasMany(RepaymentSchedule::class);
    }

    public function getRepaymentScheduleAttribute()
    {
        return $this->hasMany(RepaymentSchedule::class)->get();
    }

    public function getStatusAttribute($value)
    {
        return $this::STATUS[$value];
    }

    public static function getStatusText($value)
    {
        return WeeklyLoan::STATUS[$value];
    }

    public function changeStatus($targetStatus) : string
    {
        if (!in_array($targetStatus, array_keys(self::STATUS))) {
            return "Invalid target status.";
        }

        if ($this->status != self::STATUS[self::STATUS_PENDING]) {
            return "Loan status not PENDING ({$this->status}).";
        }

        $this->status = $targetStatus;
        $this->approved_rejected_by = auth()->user()->id;
        $this->save();
        
        return '';
    }
}
