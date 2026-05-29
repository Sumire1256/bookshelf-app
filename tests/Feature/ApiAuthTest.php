<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_user_can_login_and_get_token(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'token',
        ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password1234',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'メールアドレスまたはパスワードが正しくありません',
        ]);
    }

    public function test_login_fails_with_non_existent_email(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'メールアドレスまたはパスワードが正しくありません',
        ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/logout');

        $response->assertOk();
        $response->assertJson([
            'message' => 'ログアウトしました',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => '認証が必要です',
        ]);
    }
}
