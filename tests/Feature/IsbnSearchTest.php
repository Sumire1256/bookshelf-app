<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IsbnSearchTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_authenticated_user_can_get_book_info_by_isbn(): void
    {
        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト書籍',
                            'authors' => ['テスト著者'],
                            'description' => 'テスト説明',
                            'publishedDate' => '2024-01-01',
                            'imageLinks' => [
                                'thumbnail' => 'http://example.com/image.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->get(route('books.isbn', '9784101010014'));

        $response->assertOk();
        $response->assertJson([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
    }

    public function test_response_contains_required_keys(): void
    {
        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト書籍',
                            'authors' => ['テスト著者'],
                            'description' => 'テスト説明',
                            'publishedDate' => '2024-01-01',
                            'imageLinks' => [
                                'thumbnail' => 'http://example.com/image.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->get(route('books.isbn', '9784101010014'));

        $response->assertJsonStructure([
            'title',
            'author',
            'description',
            'published_date',
            'image_url',
        ]);
    }

    public function test_multiple_authors_are_joined_with_comma(): void
    {
        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト書籍',
                            'authors' => ['著者A', '著者B'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->get(route('books.isbn', '9784101010014'));

        $response->assertJson([
            'author' => '著者A, 著者B',
        ]);
    }

    public function test_validation_fails_when_isbn_is_less_than_13_digits(): void
    {
        $response = $this->actingAs($this->user)->get(route('books.isbn', '978410101001'));

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'ISBNは13桁の数字で入力してください',
        ]);
    }

    public function test_validation_fails_when_isbn_is_more_than_13_digits(): void
    {
        $response = $this->actingAs($this->user)->get(route('books.isbn', '97841010100140'));

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'ISBNは13桁の数字で入力してください',
        ]);
    }

    public function test_validation_fails_when_isbn_contains_non_numeric_characters(): void
    {
        $response = $this->actingAs($this->user)->get(route('books.isbn', '9784AB1010014'));

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'ISBNは13桁の数字で入力してください',
        ]);
    }

    public function test_returns_404_when_book_is_not_found(): void
    {
        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'items' => [],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->get(route('books.isbn', '9784101010014'));

        $response->assertStatus(404);
        $response->assertJson([
            'error' => '書籍が見つかりませんでした',
        ]);
    }

    public function test_returns_503_when_api_connection_fails(): void
    {
        Http::fake([
            'www.googleapis.com/*' => Http::response([], 503),
        ]);

        $response = $this->actingAs($this->user)->get(route('books.isbn', '9784101010014'));

        $response->assertStatus(503);
        $response->assertJson([
            'error' => '書籍情報の取得に失敗しました',
        ]);
    }

    public function test_returns_429_when_api_quota_is_exceeded(): void
    {
        Http::fake([
            'www.googleapis.com/*' => Http::response([], 429),
        ]);

        $response = $this->actingAs($this->user)->get(route('books.isbn', '9784101010014'));

        $response->assertStatus(429);
        $response->assertJson([
            'error' => 'APIの利用制限に達しました。しばらく時間をおいてから再度お試しください',
        ]);
    }
}
