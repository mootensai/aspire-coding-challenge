<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

abstract class FeatureTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');
        // Artisan::call('passport:install');
    }
    
    public function user1Login() {
        $userData = [
            'email' => 'user1@gmail.com',
            'password' => 'User1Password!'
        ];

        $response = $this->post('api/login',$userData, 
        ['Accept' => 'application/json']);
        return $response->json('token');
    }

    public function user2Login() {
        $userData = [
            'email' => 'user2@gmail.com',
            'password' => 'User2Password!'
        ];

        $response = $this->post('api/login',$userData, 
        ['Accept' => 'application/json']);
        return $response->json('token');
    }

    public function adminLogin() {
        $userData = [
            'email' => 'admin@aspireapp.com',
            'password' => 'Aspireadmin123!'
        ];

        $response = $this->post('api/login',$userData, 
        ['Accept' => 'application/json']);
        return $response->json('token');
    }

    public function adminApprove($hid) {
        $adminToken = $this->adminLogin();
        $this->patch('api/weekly-loans/approve/'.$hid, [],
        ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$adminToken]);
    }

    public function addLoanRequestFromUser1($loanData)
    {
        $token = $this->user1Login();

        $response = $this->post('api/weekly-loans',$loanData, 
        ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$token, 'Idempotency-Key' => Str::random(9)]);
        $result = [
            'response' => $response,
            'token' => $token
        ];
        return $result;
    }

    public function addLoanRequestFromUser2($loanData)
    {
        $token = $this->user2Login();

        $response = $this->post('api/weekly-loans',$loanData, 
        ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$token, 'Idempotency-Key' => Str::random(9)]);
        $result = [
            'response' => $response,
            'token' => $token
        ];
        return $result;
    }

    public function showLoanRequestFromUser1($hid){
        $token = $this->user1Login();
        $response = $this->get('api/weekly-loans/'.$hid, 
        ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$token]);
        return $response;
    }

    public function logout($token)
    {
        $this->post('api/logout',[], 
        ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$token]);
    }
}