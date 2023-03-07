<?php

namespace App\Jobs;

use App\Models\WeeklyLoan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoanRejectedMail;

class MailRejectedLoanRequest implements ShouldQueue
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
        $details = [
            'name' => $this->loan->user->name,
            'hid' => $this->loan->hashid
        ];

        Mail::to($this->loan->user->email)->send(new LoanRejectedMail($details));
    }
}
