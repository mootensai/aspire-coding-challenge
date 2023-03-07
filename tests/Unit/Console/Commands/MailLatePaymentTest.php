<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\MailLatePayment;
use App\Mail\PaymentLateMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Models\User;
use App\Models\WeeklyLoan;
use App\Models\RepaymentSchedule;

class MailLatePaymentTest extends TestCase
{
    /**
     * Test send email & update row info.
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
        RepaymentSchedule::factory()->count(4)->create();

        $job = new MailLatePayment();

        // action
        $job->handle();

        // assert
        $this->assertDatabaseHas('repayment_schedules', ['status' => 4]);
        Mail::assertSent(PaymentLateMail::class, function($mail) use($user, $loan){
            $mail->build();
            return $mail->hasTo($user->email) &&
                   $mail->subject === 'Payment Reminder - Loan Request '.$loan->hashid &&
                   $mail->details['name'] === $user->name &&
                   $mail->details['hid'] === $loan->hashid;
        });
    }
}
