<?php

namespace Tests\Unit\Models;

use App\Models\WeeklyLoan;
use Tests\TestCase;
use App\Models\User;

class WeeklyLoanTest extends TestCase
{

    /**
     * Test change status function to invalid status.
     *
     * @return void
     */
    public function test_changeStatusToInvalidStatus()
    {
        $data = [
            'amount' => 10000,
            'remaining' => 10000,
            'loan_term' => 3,
            'approved_rejected_by' => 1
        ];

        $user = User::factory()->create();
        $this->actingAs($user);

        $model = new WeeklyLoan($data);
        $model->remaining = $model->amount;
        $model->status = WeeklyLoan::STATUS_PENDING;
        $model->save();

        // $model = WeeklyLoan::create($data);
        $this->assertInstanceOf(WeeklyLoan::class, $model);
        $this->assertEquals($data['amount'], $model->amount);
        $this->assertEquals($data['loan_term'], $model->loan_term);

        $errorMessage = $model->changeStatus(5);
        $this->assertEquals("Invalid target status.", $errorMessage);
    }

    /**
     * Test change status function to a valid status.
     *
     * @return void
     */
    public function test_changeStatusToValidStatus()
    {
        $data = [
            'amount' => 10000,
            'loan_term' => 3
        ];

        $user = User::factory()->create();
        $this->actingAs($user);

        $model = new WeeklyLoan($data);
        $model->remaining = $model->amount;
        $model->status = WeeklyLoan::STATUS_PENDING;
        $model->save();

        // $model = WeeklyLoan::create($data);
        $this->assertInstanceOf(WeeklyLoan::class, $model);
        $this->assertEquals($data['amount'], $model->amount);
        $this->assertEquals($data['loan_term'], $model->loan_term);

        $errorMessage = $model->changeStatus(WeeklyLoan::STATUS_APPROVED);
        $this->assertEquals("", $errorMessage);
        $data['status'] = WeeklyLoan::STATUS_APPROVED;
        $data['remaining'] = $model->amount;
        $data['created_by'] = 1;
        $data['updated_by'] = 1;
        $data['approved_rejected_by'] = 1;
        $this->assertDatabaseHas('weekly_loans',  
            $data
        );
    }
}
