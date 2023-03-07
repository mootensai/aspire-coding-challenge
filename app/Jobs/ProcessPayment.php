<?php

namespace App\Jobs;

use App\Mail\AllInstallmentSettledMail;
use App\Mail\PaymentSuccessMail;
use App\Models\Payment;
use App\Models\RepaymentSchedule;
use App\Models\WeeklyLoan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Payment $payment;
    protected WeeklyLoan $weeklyLoan;

    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
        $this->weeklyLoan = $payment->weeklyLoan;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();
        $detail = [];
        try {
            $this->updateWeeklyLoan();
            $detail = $this->updateRepaymentSchedule();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        if ($detail['lastScheduleStatus'] == RepaymentSchedule::STATUS[RepaymentSchedule::STATUS_APPROVED]) {
            Mail::to($this->weeklyLoan->user->email)->send(new PaymentSuccessMail($detail));
        }
        else 
        {
            Mail::to($this->weeklyLoan->user->email)->send(new AllInstallmentSettledMail($detail));
        }
    }

    private function updateWeeklyLoan()
    {
        $weeklyLoan = $this->payment->WeeklyLoan;
        $weeklyLoan->remaining -= $this->payment->amount;
        $weeklyLoan->updated_by = $weeklyLoan->created_by;
        if (!$weeklyLoan->remaining) {
            $weeklyLoan->status = WeeklyLoan::STATUS_PAID;
        }

        $weeklyLoan->save();
    }

    private function updateRepaymentSchedule()
    {
        $weeklyLoan = $this->payment->weeklyLoan;
        $numberOfRepaymentScheduleToBeUpdated = ceil($this->payment->amount / ($weeklyLoan->amount / $weeklyLoan->loan_term));
        if (ceil($this->payment->amount % ($weeklyLoan->amount / $weeklyLoan->loan_term)) == 0) {
            $numberOfRepaymentScheduleToBeUpdated++;
        }

        $repaymentSchedules = RepaymentSchedule::where('weekly_loan_id', $weeklyLoan->id)
            ->where('status', RepaymentSchedule::STATUS_APPROVED)
            ->orderBy('id')
            ->limit($numberOfRepaymentScheduleToBeUpdated)
            ->get();
        
        foreach ($repaymentSchedules->all() as $repaymentSchedule) {
            $amountToPayOneSchedule = $repaymentSchedule->remaining;
            if (round($repaymentSchedule->remaining - $this->payment->amount) > 0) {
                $repaymentSchedule->remaining -= $this->payment->amount;
            } else {
                $repaymentSchedule->remaining = 0;
                $repaymentSchedule->status = RepaymentSchedule::STATUS_PAID;
            }
            
            $repaymentSchedule->save();
            $this->payment->amount -= $amountToPayOneSchedule;

            $detail = [
                'hid' => $weeklyLoan->hashid,
                'name' => $weeklyLoan->user->name,
                'nextPaymentDate' => $repaymentSchedule->due_date,
                'remaining' => $repaymentSchedule->remaining,
                'lastScheduleStatus' => $repaymentSchedule->status
            ];
        }
            
        return $detail;
    }
}
