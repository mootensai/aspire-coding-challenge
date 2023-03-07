<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\FeatureTestCase;

class WeeklyLoanControllerApproveTest extends FeatureTestCase
{
    /**
     * Test approve with invalid credential
     *
     * @return void
     */
    public function test_ApproveWithInvalidCredential()
    {
        $loanData = [
            'amount' => '10000',
            'loan_term' => 3
        ];
        $loanResponseAndToken = $this->addLoanRequestFromUser1($loanData);
        $hid = $loanResponseAndToken['response']->json('data.hashid');
        $token = $loanResponseAndToken['token'];
        $response = $this->patch('api/weekly-loans/approve/'.$hid, 
        ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$token]);

        $response
        ->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * Test approve with invalid credential
     *
     * @return void
     */
    public function test_ApproveWithInvalidHashId()
    {
        $adminToken = $this->adminLogin();
        $response = $this->patch('api/weekly-loans/approve/any_id', 
        ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$adminToken]);

        $response
        ->assertStatus(404);
    }

    /**
     * Test approve with valid credential
     *
     * @return void
     */
    public function test_ApproveWithValidCredential()
    {
        $loanData = [
            'amount' => '10000',
            'loan_term' => 3
        ];
        $loanResponseAndToken = $this->addLoanRequestFromUser1($loanData);
        $hid = $loanResponseAndToken['response']->json('data.hashid');
        $adminToken = $this->adminLogin();
        $response = $this->patch('api/weekly-loans/approve/'.$hid, [],
        ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$adminToken]);

        $response
        ->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'OK'
        ]);
    }
}
