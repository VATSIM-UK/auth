<?php


namespace Tests\Feature;


use App\Passport\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccessTokenTest extends TestCase
{
    use DatabaseTransactions;

    private $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = factory(Client::class)->create([
            'redirect' => 'http://example.org/callback',
            'personal_access_client' => false,
            'password_client' => false,
        ]);
    }

    /** @test */
    public function testCanRequestAuthorization()
    {
        $state = Str::random(40);

        $query = http_build_query([
            'client_id' => $this->client->id,
            'redirect_uri' => 'http://example.org/callback',
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);

        $this->get('oauth/authorize?'.$query)
            ->assertRedirect('login');
    }

    /** @test */
    public function testGetsSentBackToCallbackWithoutApprovalForFirstParty()
    {
        $state = Str::random(40);

        $query = http_build_query([
            'client_id' => $this->client->id,
            'redirect_uri' => 'http://example.org/callback',
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);

        $res = $this->actingAs($this->user)
            ->get('/oauth/authorize?'.$query);

        $this->assertContains('http://example.org/callback', $res->headers->get('location'));

        $parts = parse_url($res->headers->get('location'));
        parse_str($parts['query'], $query);

        $this->assertEquals($state, $query['state']);

        $this->post('oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
            'redirect_uri' => 'http://example.org/callback',
            'code' => $query['code'],
        ])->assertJsonStructure([
            'token_type',
            'expires_in',
            'access_token',
            'refresh_token'
        ]);
    }

}
