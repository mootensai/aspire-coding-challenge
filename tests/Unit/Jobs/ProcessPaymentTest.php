<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessPayment;
use App\Mail\PaymentSuccessMail;
use App\Mail\AllInstallmentSettledMail;
use App\Models\Payment;
use App\Models\RepaymentSchedule;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Models\User;
use App\Models\WeeklyLoan;

class ProcessPaymentTest extends TestCase
{
    /**
     * Test process payment without finishing all installment.
     *
     * @return void
     */
    public function test_RunJobLoanNotFinished()
    {
        // arrange
        Mail::fake();
        $user = User::factory()->create();
        $this->actingAs($user);
        $loan = WeeklyLoan::factory()->has(RepaymentSchedule::factory()->count(4))->create();
        $payment = Payment::create([
            'weekly_loan_id' => $loan->id,
            'amount' => 5000
        ]);

        $job = new ProcessPayment($payment);

        // action
        $job->handle();

        // assert
        $this->assertDatabaseHas('weekly_loans', ['status' => 1, 'remaining' => 5000]);
        $this->assertDatabaseHas('repayment_schedules', ['status' => 3, 'remaining' => 0]);
        Mail::assertSent(PaymentSuccessMail::class, function($mail) use($user, $loan){
            $mail->build();
            return $mail->hasTo($user->email) &&
                   $mail->subject === 'Confirmation of Payment for Loan Request ' . $loan->hashid . 'and Next Payment Due.' &&
                   $mail->details['name'] === $user->name &&
                   $mail->details['hid'] === $loan->hashid;
        });
    }

    /**
     * Test process payment with finishing all installment.
     *
     * @return void
     */
    public function test_RunJobLoanFinished()
    {
        // arrange
        Mail::fake();
        $user = User::factory()->create();
        $this->actingAs($user);
        $loan = WeeklyLoan::factory()->has(RepaymentSchedule::factory()->count(4))->create();
        $payment = Payment::create([
            'weekly_loan_id' => $loan->id,
            'amount' => 10000
        ]);

        $job = new ProcessPayment($payment);

        // action
        $job->handle();

        // assert
        $this->assertDatabaseHas('weekly_loans', ['status' => 3, 'remaining' => 0]);
        $this->assertDatabaseHas('repayment_schedules', ['status' => 3, 'remaining' => 0]);
        Mail::assertSent(AllInstallmentSettledMail::class, function($mail) use($user, $loan){
            $mail->build();
            return $mail->hasTo($user->email) &&
                   $mail->subject === 'Congratulations and Thank You for Paying Off Your Loan ID '.$loan->hashid. '.' &&
                   $mail->details['name'] === $user->name &&
                   $mail->details['hid'] === $loan->hashid;
        });
    }
}
