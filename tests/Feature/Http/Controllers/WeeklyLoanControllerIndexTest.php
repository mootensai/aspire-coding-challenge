<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\FeatureTestCase;

class WeeklyLoanControllerIndexTest extends FeatureTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_indexWithoutLogin()
    {
        $response = $this->get('api/weekly-loans', ['Accept' => 'application/json']);

        $response->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_index()
    {
        $token = $this->user1Login();
        $response = $this->get('api/weekly-loans', ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$token]);

        $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => "OK",
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
    }
}
