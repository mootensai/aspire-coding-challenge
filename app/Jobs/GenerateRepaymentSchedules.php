<?php

namespace App\Jobs;

use App\Mail\LoanApprovedMail;
use App\Models\RepaymentSchedule;
use App\Models\WeeklyLoan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Background job to handle generate repayment schedules.
 */
class GenerateRepaymentSchedules implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected WeeklyLoan $loan;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WeeklyLoan $loan)
    {
        $this->loan = $loan;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        // generate repayment schedule
        DB::transaction(function () {
            $loanTerm = $this->loan->loan_term;
            $amount = $this->loan->amount;
            $paymentPerTerm = $amount / $loanTerm;
            $today = Date::today();
            for($i = 1; $i <= $loanTerm; $i++) {
                $nextWeek = $today->addWeek();
                $duePayment = $i != $loanTerm ? $paymentPerTerm : $this->round_up($amount, 2);
                RepaymentSchedule::create([
                    'due_date' => $nextWeek->toDateString(),
                    'due_payment' => $duePayment,
                    'remaining' => $duePayment,
                    'weekly_loan_id' => $this->loan->id,
                    'status' => WeeklyLoan::STATUS_APPROVED
                ]);
                $amount -= $duePayment;
                $today = $nextWeek;
            }
        });

        // if everything is success, then send an email
        $details = [
            'name' => $this->loan->user->name,
            'hid' => $this->loan->hashid
        ];

        Mail::to($this->loan->user->email)->send(new LoanApprovedMail($details));
    }

    // https://stackoverflow.com/questions/8239600/rounding-up-to-the-second-decimal-place
    private function round_up ($value, $precision) { 
        $pow = pow ( 10, $precision ); 
        return ( ceil ( $pow * $value ) + ceil ( $pow * $value - ceil ( $pow * $value ) ) ) / $pow; 
    } 
}
