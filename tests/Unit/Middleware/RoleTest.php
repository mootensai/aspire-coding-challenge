<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class RoleTest extends TestCase
{
    /**
     * Test making an unauthenticated request.
     *
     * @return void
     */
    public function test_HandleUnauthenticatedRequest()
    {
        $request = Request::create('api/weekly-loans', 'GET');

        $middleware = new Role();

        $response = $middleware->handle($request, function() {});

        $this->assertInstanceOf(JsonResponse::class, $response);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('{"message":"Unauthenticated."}', $response->getContent());
    }
}
