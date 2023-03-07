<?php

namespace Tests\Unit\Jobs;

use App\Jobs\MailRejectedLoanRequest;
use App\Mail\LoanRejectedMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Models\User;
use App\Models\WeeklyLoan;

class MailRejectedLoanRequestTest extends TestCase
{
    /**
     * Test mail customer that the loan request is rejected.
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
        $job = new MailRejectedLoanRequest($loan);

        // action
        $job->handle();

        // assert
        Mail::assertSent(LoanRejectedMail::class, function($mail) use($user, $loan){
            $mail->build();
            return $mail->hasTo($user->email) &&
                   $mail->subject === 'We\'re sorry, Your Loan Request is Rejected.' &&
                   $mail->details['name'] === $user->name &&
                   $mail->details['hid'] === $loan->hashid;
        });
    }
}
