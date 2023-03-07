<?php

namespace Tests\Unit\Jobs;

use App\Jobs\MailLoanRequestCreated;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Models\User;
use App\Models\WeeklyLoan;
use App\Mail\LoanRequestCreatedMail;

class MailLoanRequestCreatedTest extends TestCase
{
    /**
     * Test mail customer that the loan request is created.
     *
     * @return void
     */
    public function test_RunJob()
    {
        // arrange
        Mail::fake();
        $user = User::factory()->create();
        $this->actingAs($user);
        $loan = WeeklyLoan::factory()->create();
        $job = new MailLoanRequestCreated($loan);

        // action
        $job->handle();

        // assert
        Mail::assertSent(LoanRequestCreatedMail::class, function($mail) use($user, $loan){
            $mail->build();
            return $mail->hasTo($user->email) &&
                   $mail->subject === 'Your Loan Request is Being Processed.' &&
                   $mail->details['name'] === $user->name &&
                   $mail->details['hid'] === $loan->hashid;
        });
    }
}
