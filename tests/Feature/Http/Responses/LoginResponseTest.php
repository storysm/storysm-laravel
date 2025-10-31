<?php

namespace Tests\Feature\Http\Responses;

use App\Http\Responses\LoginResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class LoginResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('app.url', 'http://localhost:8000');
        Config::set('fortify.home', '/dashboard');
    }

    public function test_redirects_to_valid_next_url_on_same_host(): void
    {
        $request = Request::create('/login', 'POST', ['next' => 'http://localhost/profile']);
        $response = (new LoginResponse)->toResponse($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://localhost/profile', $response->headers->get('Location'));
    }

    public function test_ignores_external_next_url_and_redirects_to_default(): void
    {
        $request = Request::create('/login', 'POST', ['next' => 'http://external.com/malicious']);
        $response = (new LoginResponse)->toResponse($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://localhost:8000/dashboard', $response->headers->get('Location'));
    }

    public function test_redirects_to_default_when_next_url_is_empty(): void
    {
        $request = Request::create('/login', 'POST', ['next' => '']);
        $response = (new LoginResponse)->toResponse($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://localhost:8000/dashboard', $response->headers->get('Location'));
    }

    public function test_redirects_to_default_when_next_url_is_malformed(): void
    {
        $request = Request::create('/login', 'POST', ['next' => 'http:///malformed']);
        $response = (new LoginResponse)->toResponse($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://localhost:8000/dashboard', $response->headers->get('Location'));
    }

    public function test_redirects_to_default_when_next_url_has_different_subdomain(): void
    {
        $request = Request::create('/login', 'POST', ['next' => 'http://sub.localhost/profile']);
        $response = (new LoginResponse)->toResponse($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://localhost:8000/dashboard', $response->headers->get('Location'));
    }
}
