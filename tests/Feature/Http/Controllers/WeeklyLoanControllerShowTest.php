<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\FeatureTestCase;

class WeeklyLoanControllerShowTest extends FeatureTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_showWithInvalidCredential()
    {
        $loanData = [
            'amount' => 10000,
            'loan_term' => 3
        ];
        $loanResponseAndToken = $this->addLoanRequestFromUser1($loanData);
        $hid = $loanResponseAndToken['response']->json('data.hashid');
        $token = $this->user2Login();

        $response = $this->get('api/weekly-loans/'.$hid, 
        ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$token]);

        $response
        ->assertStatus(404);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_showWithInvalidHashid()
    {
        $token = $this->user1Login();

        $response = $this->get('api/weekly-loans/any_hid', 
        ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$token]);

        $response
        ->assertStatus(404);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_showWithValidHashId()
    {
        $loanData = [
            'amount' => '10000',
            'loan_term' => 3
        ];
        $loanResponseAndToken = $this->addLoanRequestFromUser1($loanData);
        $hid = $loanResponseAndToken['response']->json('data.hashid');
        $token = $loanResponseAndToken['token'];
        $response = $this->get('api/weekly-loans/'.$hid, 
        ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$token]);


        $response
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'OK'
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
                'created_at',
                'updated_at',
                'hashid',
                'RepaymentSchedule'
            ]
        ]);
    }
}
