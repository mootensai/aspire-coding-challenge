<?php

namespace App\Console\Commands;

use App\Mail\PaymentLateMail;
use App\Models\RepaymentSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailLatePayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MailLatePayment:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $latePayments = RepaymentSchedule::whereDate('due_date', '<', now())
        ->where('status', RepaymentSchedule::STATUS_APPROVED)
        ->where('times_reminded', '<', env('MAIL_LATE_PAYMENT_THRESHOLD'))
        ->groupBy('weekly_loan_id')
        ->get();
        foreach ($latePayments->all() as $latePayment) {
            $lateInDays = new Carbon($latePayment->due_date);
            $lateInDays = $lateInDays->diff(Carbon::now())->days;
            $details = [
                'hid' => $latePayment->weeklyLoan->hashid,
                'name' => $latePayment->weeklyLoan->user->name,
                'remaining' => $latePayment->remaining,
                'lateInDays' => abs($lateInDays)
            ];
            Mail::to($latePayment->weeklyLoan->user->email)->send(new PaymentLateMail($details));

            $latePayment->times_reminded += 1;
            $latePayment->status = RepaymentSchedule::STATUS_LATE;
            $latePayment->save();
        }
    }
}
