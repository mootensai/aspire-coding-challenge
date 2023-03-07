<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\FeatureTestCase;

class LoginControllerTest extends FeatureTestCase
{
    /**
     * Test LoginController required Validator.
     *
     * @return void
     */
    public function test_loginRequiredFields()
    {
        $this->post('api/login',[], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson([
                "email" => [
                        "The email field is required."
                    ],
                "password" => [
                    "The password field is required."
                ]
            ]);
    }

    /**
     * Test login with invalid credential.
     *
     * @return void
     */
    public function test_loginWithInvalidCredential()
    {
        $userData = [
            'email' => 'invalid.email@mail.com',
            'password' => 'invalidPassword!'
        ];
        
        $this->post('api/login',$userData, ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Incorrect email or password.'
            ]);
    }

    /**
     * Test login with valid user credential.
     *
     * @return void
     */
    public function test_loginWithValidCredential()
    {
        $userData = [
            'email' => 'user1@gmail.com',
            'password' => 'User1Password!'
        ];
    
        $this->post('api/login',$userData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'success',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'is_admin',
                    'created_at',
                    'updated_at'
                ],
                'token'
            ])
            ->assertSeeText('user1@gmail.com')
            ->assertSeeText('token');
    }

    /**
     * Test login with valid admin credential.
     *
     * @return void
     */
    public function test_loginWithAdminCredential()
    {
        $userData = [
            'email' => 'admin@aspireapp.com',
            'password' => 'Aspireadmin123!'
        ];
    
        $this->post('api/login',$userData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'success',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'is_admin',
                    'created_at',
                    'updated_at'
                ],
                'token'
            ])
            ->assertSeeText('admin@aspireapp.com')
            ->assertSeeText('"is_admin":"1"', false)
            ->assertSeeText('token');
    }
}
