<?php


namespace Tests\Unit\Middleware;

use App\Http\Middleware\RequirePasswordMiddleware;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Tests\TestCase;

class RequirePasswordMiddlewareTest extends TestCase
{
    /** @test */
    public function itAsksForConfirmationIfHasPassword()
    {
        $this->user->setPassword("Testing1234");
        $this->assertTrue($this->user->hasPassword());

        $this->actingAs($this->user);

        $response = $this->mockRequest();

        $this->assertEquals($response->getStatusCode(), 423);
        $this->assertEquals($response->getData()->message, 'Password confirmation required.');
    }

    /** @test */
    public function itSkipsMiddlewareIfHasNoPassword()
    {
        $this->actingAs($this->user);
        $this->assertFalse($this->user->hasPassword());

        $response = $this->mockRequest();
        $this->assertNull($response);
    }

    /** @test */
    public function itThrowsExceptionIfUserNotAuthenticated()
    {
        $this->expectException(UnauthorizedException::class);
        $response = $this->mockRequest();
    }

    private function mockRequest()
    {
        $requestMock = \Mockery::mock(Request::class);
        $requestMock->shouldReceive('session')
            // just return an anonymous dummy class that knows the has() method and
            // returns true or false depending on our needs. Alternative would be
            // to also mock the session and return the session mock.
            ->andReturn(new class
            {
                public function get(string $key)
                {
                    return true; // or false, depends on what you want to test
                }
            });
        $requestMock->shouldReceive('expectsJson')->andReturn(true);
        $requestMock->makePartial();
        $middleware = resolve(RequirePasswordMiddleware::class);
        return $middleware->handle($requestMock, function () {
        });
    }
}
