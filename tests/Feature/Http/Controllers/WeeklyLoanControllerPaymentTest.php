<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\FeatureTestCase;
use Illuminate\Support\Str;

class WeeklyLoanControllerPaymentTest extends FeatureTestCase
{
    /**
     * Test user pay before admin approve the loan request.
     *
     * @return void
     */
    public function test_PaymentBeforeApproved()
    {
        $loanData = [
            'amount' => '10000',
            'loan_term' => 3
        ];
        $loanResponseAndToken = $this->addLoanRequestFromUser1($loanData);
        $hid = $loanResponseAndToken['response']->json('data.hashid');
        $token = $loanResponseAndToken['token'];

        $response = $this->post('api/weekly-loans/pay/'.$hid, [],
        ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$token, 'Idempotency-Key' => Str::random(9)]);

        $response
        ->assertStatus(422)
        ->assertJson(['message' => 'Loan status is still PENDING.']);
    }

    /**
     * Test user 2 pay loan request owned by user 1.
     *
     * @return void
     */
    public function test_PaymentWithInvalidCredential()
    {
        $loanData = [
            'amount' => '10000',
            'loan_term' => 3
        ];
        $loanResponseAndToken = $this->addLoanRequestFromUser1($loanData);
        $hid = $loanResponseAndToken['response']->json('data.hashid');
        $this->adminApprove($hid);

        $user2Token = $this->user2Login();

        $paymentData = [
            'amount' => '10000',
        ];

        $response = $this->post('api/weekly-loans/pay/'.$hid, $paymentData,
        ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$user2Token, 'Idempotency-Key' => Str::random(9)]);

        $response
        ->assertStatus(404);
    }

    /**
     * Test pay with lesser amount than due payment.
     *
     * @return void
     */
    public function test_PaymentWithLesserThanDuePayment()
    {
        $loanAmount = 10000;
        $loanTerm = 3;
        $paymentAmount = 1000; 
        $expectedRemaining = [
            round(abs(($loanAmount / $loanTerm) - $paymentAmount), 2),
        ];
        $expectedStatus = ['APPROVED', 'APPROVED', 'APPROVED'];
        $this->makePayment(
            $loanAmount, 
            $loanTerm, 
            $paymentAmount, 
            $expectedRemaining, 
            $expectedStatus
        );
    }

    /**
     * Test pay with amount equal to due payment.
     *
     * @return void
     */
    public function test_PaymentWithEqualWithDuePayment()
    {
        $loanAmount = 10000;
        $loanTerm = 3;
        $paymentAmount = 3333.33; 
        $expectedRemaining = [
            0
        ];
        $expectedStatus = ['PAID', 'APPROVED', 'APPROVED'];
        $this->makePayment(
            $loanAmount, 
            $loanTerm, 
            $paymentAmount, 
            $expectedRemaining, 
            $expectedStatus
        );
    }

    /**
     * Test pay with amount greater than due payment.
     *
     * @return void
     */
    public function test_PaymentWithGreaterThanDuePayment()
    {
        $loanAmount = 10000;
        $loanTerm = 3;
        $paymentAmount = 5000; 
        $expectedRemaining = [
            0,
            round(abs(($loanAmount / $loanTerm) - $paymentAmount), 2)
        ];
        $expectedStatus = ['PAID', 'APPROVED', 'APPROVED'];
        $this->makePayment(
            $loanAmount, 
            $loanTerm, 
            $paymentAmount, 
            $expectedRemaining, 
            $expectedStatus
        );
    }

    /**
     * Test pay with amount double than the due payment.
     *
     * @return void
     */
    public function test_PaymentWithDoubleTheDuePayment()
    {
        $loanAmount = 10000;
        $loanTerm = 3;
        $paymentAmount = 6666.67; 
        $expectedRemaining = [
            0,
            0
        ];
        $expectedStatus = ['PAID', 'PAID', 'APPROVED'];
        $this->makePayment(
            $loanAmount, 
            $loanTerm, 
            $paymentAmount, 
            $expectedRemaining, 
            $expectedStatus
        );
    }

    /**
     * Test pay with full payment.
     *
     * @return void
     */
    public function test_PaymentWithFullPayment()
    {
        $loanAmount = 10000;
        $loanTerm = 3;
        $paymentAmount = 10000; 
        $expectedRemaining = [
            0,
            0,
            0
        ];
        $expectedStatus = ['PAID', 'PAID', 'PAID'];
        $this->makePayment(
            $loanAmount, 
            $loanTerm, 
            $paymentAmount, 
            $expectedRemaining, 
            $expectedStatus
        );
    }

    /**
     * Function to simplify payment process.
     *
     * @param float $loanAmount
     * @param int $loanTerm
     * @param float $paymentAmount
     * @param array $expectedRemaining
     * @param array $expectedStatus
     * @return void
     */
    private function makePayment(
        $loanAmount, 
        $loanTerm, 
        $paymentAmount, 
        array $expectedRemaining,
        array $expectedStatus)
    {
        $loanData = [
            'amount' => $loanAmount,
            'loan_term' => $loanTerm
        ];
        $loanResponseAndToken = $this->addLoanRequestFromUser1($loanData);
        $hid = $loanResponseAndToken['response']->json('data.hashid');
        $this->adminApprove($hid);

        $user2Token = $this->user1Login();

        $paymentData = [
            'amount' => $paymentAmount,
        ];

        $response = $this->post('api/weekly-loans/pay/'.$hid, $paymentData,
        ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$user2Token, 'Idempotency-Key' => Str::random(9)]);

        $response
        ->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'amount' => $paymentAmount
            ]
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'amount',
                'updated_at',
                'created_at'
            ]
        ]);

        $responseShowJson = $this->showLoanRequestFromUser1($hid)->json();

        $this->assertEquals(
            $loanAmount - $paymentAmount, 
            round($responseShowJson['data']['remaining'], 2)
        );
        
        foreach ($expectedRemaining as $key => $value) {
            $this->assertEquals(
                $value, 
                round($responseShowJson['data']['RepaymentSchedule'][$key]['remaining'], 2)
            );
        }

        foreach ($expectedStatus as $key => $value) {
            $this->assertEquals(
                $value,
                $responseShowJson['data']['RepaymentSchedule'][$key]['status']
            );
        }
    }
}
