<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\FeatureTestCase;

class WeeklyLoanControllerStoreTest extends FeatureTestCase
{

    public function test_requiredValidator()
    {
        $loanData = [
        ];
        $response = $this->addLoanRequestFromUser1($loanData);

        $response['response']
        ->assertStatus(422)
        ->assertJson([
            "amount" => [
                "The amount field is required."
            ],
            "loan_term" => [
                "The loan term field is required."
            ]
        ]);
    }

    public function test_minimumAmountValidator()
    {
        $loanData = [
            'amount' => 99,
            'loan_term' => 3
        ];
        $response = $this->addLoanRequestFromUser1($loanData);

        $response['response']
        ->assertStatus(422)
        ->assertJson([
            "amount" => [
                "The amount must be at least 100."
            ]
        ]);
    }

    public function test_maximumAmountValidator()
    {
        $loanData = [
            'amount' => 1000001,
            'loan_term' => 3
        ];
        $response = $this->addLoanRequestFromUser1($loanData);

        $response['response']
        ->assertStatus(422)
        ->assertJson([
            "amount" => [
                "The amount must not be greater than 1000000."
            ]
        ]);
    }

    public function test_MinimumLoanTermValidator()
    {
        $loanData = [
            'amount' => 10000,
            'loan_term' => 1
        ];
        $response = $this->addLoanRequestFromUser1($loanData);

        $response['response']
        ->assertStatus(422)
        ->assertJson([
            "loan_term" => [
                "The loan term must be at least 2."
            ]
        ]);
    }

    public function test_MaximumLoanTermValidator()
    {
        $loanData = [
            'amount' => 10000,
            'loan_term' => 53
        ];
        $response = $this->addLoanRequestFromUser1($loanData);

        $response['response']
        ->assertStatus(422)
        ->assertJson([
            "loan_term" => [
                "The loan term must not be greater than 52."
            ]
        ]);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_AddNewLoanRequestsuccessfully()
    {
        $loanData = [
            'amount' => '10000.00',
            'loan_term' => 3
        ];
        $response = $this->addLoanRequestFromUser1($loanData);

        $response['response']
        ->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => "OK",
            'data' => [
                'amount' => $loanData['amount'],
                'loan_term' => $loanData['loan_term']
            ]
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'amount',
                'loan_term',
                'remaining',
                'status',
                'created_by',
                'updated_by',
                'updated_at',
                'created_at',
                'hashid',
                'RepaymentSchedule'
            ]
        ]);
    }
}
