<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\FeatureTestCase;

class LogoutControllerTest extends FeatureTestCase
{

    /**
     * Test logout without any token.
     *
     * @return void
     */
    public function test_logoutFail()
    {
        $this->post('api/logout', [], ['Accept' => 'application/json'])
        ->assertJson([
            'success' => false,
            'message' => 'A token is required.'
        ]);
    }

    /**
     * Test logout with valid token.
     *
     * @return void
     */
    public function test_logoutSuccess()
    {
        $token = $this->user1Login();
        $this->post('api/logout', [], ['Accept' => 'application/json', "Authorization" => "Bearer ". $token])
        ->assertJson([
            'success' => true,
            'message' => 'Logout success.'
        ]);
    }
}
