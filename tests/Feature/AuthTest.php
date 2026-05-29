<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // 会員登録機能
    public function test_register_validation_message_displayed_when_name_is_empty(): void
    {
        $response = $this->post(route('register'), [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertContains('お名前を入力してください', session()->get('errors')->get('name'));
    }

    public function test_register_validation_message_displayed_when_email_is_empty(): void
    {
        $response = $this->post(route('register'), [
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'name' => 'テスト名前',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertContains('メールアドレスを入力してください', session()->get('errors')->get('email'));
    }

    public function test_register_validation_message_displayed_when_password_is_less_than_8_characters(): void
    {
        $response = $this->post('/register', [
            'email' => 'test@example',
            'password' => 'pass',
            'password_confirmation' => 'pass',
            'name' => 'テスト名前',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertContains('パスワードは8文字以上で入力してください', session()->get('errors')->get('password'));
    }

    public function test_register_validation_message_displayed_when_password_confirmation_does_not_match(): void
    {
        $response = $this->post('/register', [
            'email' => 'test@example',
            'password' => 'password123',
            'password_confirmation' => 'password456',
            'name' => 'テスト名前',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertContains('パスワードと一致しません', session()->get('errors')->get('password'));
    }

    public function test_register_validation_message_displayed_when_password_is_empty(): void
    {
        $response = $this->post('/register', [
            'email' => 'test@example',
            'password' => '',
            'password_confirmation' => 'password123',
            'name' => 'テスト名前',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertContains('パスワードを入力してください', session()->get('errors')->get('password'));
    }

    public function test_register_data_saved_successfully_when_form_is_filled_correctly(): void
    {
        $response = $this->post('/register', [
            'email' => 'test@example',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'name' => 'テスト名前',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'テスト名前',
            'email' => 'test@example',
        ]);
    }

    // ログイン機能
    public function test_authenticated_user_is_redirected_from_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/');
    }

    public function test_login_validation_message_displayed_when_email_is_empty(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertContains('メールアドレスを入力してください', session()->get('errors')->get('email'));
    }

    public function test_login_validation_message_displayed_when_password_is_empty(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertContains('パスワードを入力してください', session()->get('errors')->get('password'));
    }

    public function test_login_validation_message_displayed_when_registration_data_mismatch(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $errors = session('errors')->get('email');
        $this->assertContains('ログイン情報が登録されていません', $errors);
    }

    // ログイン試行のテスト
    public function test_login_is_rate_limited_after_5_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        collect(range(1, 6))->each(function () {
            $this->post(route('login'), [
                'email' => 'test@example.com',
                'password' => 'wrong_password',
            ]);
        });

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(429);
    }

    // ログアウト機能
    public function test_redirected_to_login_when_user_logout(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
