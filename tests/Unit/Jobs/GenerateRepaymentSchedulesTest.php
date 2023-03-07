<?php

namespace Tests\Unit\Jobs;

use App\Mail\LoanApprovedMail;
use App\Jobs\GenerateRepaymentSchedules;
use App\Models\RepaymentSchedule;
use App\Models\WeeklyLoan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Models\User;

class GenerateRepaymentSchedulesTest extends TestCase
{
    /**
     * Test generate payment schedules.
     *
     * @return void
     */
    public function test_RunJob()
    {
        // arrange
        Mail::fake();
        $user = User::factory()->create();
        $this->actingAs($user);
        $loan = WeeklyLoan::factory()->create([
            'id' => 1,
            'amount' => 10000.00,
            'loan_term' => 3,
            'remaining' => 10000.00,
            'status' => 0,
            'created_by' => 1,
            'updated_by' => 1,
            'approved_rejected_by' => null,
            'created_at' => '2023-03-06 00:00:00',
            'updated_at' => '2023-03-06 00:00:00'
        ]);

        $job = new GenerateRepaymentSchedules($loan);

        //action
        $job->handle();

        // assert
        $this->assertDatabaseCount('repayment_schedules', $loan->loan_term);
        $this->assertDatabaseHas('repayment_schedules', [
            'due_payment' => 3333.3333333333
        ]);
        $this->assertDatabaseHas('repayment_schedules', [
            'due_payment' => 3333.34
        ]);
        Mail::assertSent(LoanApprovedMail::class, function($mail) use($user, $loan){
            $mail->build();
            return $mail->hasTo($user->email) &&
                   $mail->subject === 'Your Loan Request Approved!' &&
                   $mail->details['name'] === $user->name &&
                   $mail->details['hid'] === $loan->hashid;
        });
    }
}
