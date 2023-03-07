<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\FeatureTestCase;

class RegisterControllerTest extends FeatureTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_registerRequiredFields()
    {
        $this->post('api/register', [], ['Accept' => "application/json"])
        ->assertStatus(422)
        ->assertJson([
            'name' => [
                'The name field is required.'
            ],
            'email' => [
                'The email field is required.'
            ],
            'password' => [
                'The password field is required.'
            ],
            'password_confirmation' => [
                'The password confirmation field is required.'
            ]
        ]);
    }

    public function test_registerWithWeakPassword()
    {
        $userData = [
            'name' => 'Test',
            'email' => 'test@gmail.com',
            'password' => 'testing',
            'password_confimation' => 'testing'
        ];

        $this->post('api/register', $userData, ['Accept' => "application/json"])
        ->assertStatus(422)
        ->assertJson([
            'password' => [
                "The password must be at least 8 characters.",
                "The password must contain at least one uppercase and one lowercase letter.",
                "The password must contain at least one symbol.",
                "The password must contain at least one number."
            ],
        ]);
    }

    public function test_registerWithLeakPassword()
    {
        $userData = [
            'name' => 'Test',
            'email' => 'test@gmail.com',
            'password' => 'Testing123!',
            'password_confimation' => 'Testing123!'
        ];

        $this->post('api/register', $userData, ['Accept' => "application/json"])
        ->assertStatus(422)
        ->assertJson([
            'password' => [
                "The given password has appeared in a data leak. Please choose a different password."
            ],
        ]);
    }

    public function test_registerInvalidPasswordConfirmation()
    {
        $userData = [
            'name' => 'Test',
            'email' => 'test@gmail.com',
            'password' => 'myUnleakedPassword123!',
            'password_confirmation' => 'testing'
        ];

        $this->post('api/register', $userData, ['Accept' => "application/json"])
        ->assertStatus(422)
        ->assertJson([
            'password_confirmation' => [
                "The password confirmation and password must match."
            ],
        ]);
    }

    public function test_registerValidUser()
    {
        $userData = [
            'name' => 'Test',
            'email' => 'test@gmail.com',
            'password' => 'myUnleakedPassword123!',
            'password_confirmation' => 'myUnleakedPassword123!'
        ];

        $this->post('api/register', $userData, ['Accept' => "application/json"])
        ->assertStatus(201)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'success',
            'user' => [
                'name',
                'email',
                'updated_at',
                'created_at',
                'id'
            ]
        ]);
    }
}

