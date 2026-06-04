<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiBookTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $book;

    private $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->genre = Genre::factory()->create();
        $this->book = Book::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->book->genres()->attach($this->genre->id);
    }

    public function test_guest_can_get_book_list_with_correct_structure(): void
    {
        Book::factory()->count(3)->create()->each(function ($book) {
            $book->genres()->attach($this->genre->id);
        });

        $response = $this->getJson('/api/v1/books');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'genres',
                    'image_url',
                    'reviews_avg_rating',
                    'reviews_count',
                ],
            ],
            'links',
            'meta',
        ]);
    }

    public function test_book_detail_shows_multiple_reviews(): void
    {
        Review::factory()->create([
            'book_id' => $this->book,
            'rating' => 5,
        ]);
        Review::factory()->create([
            'book_id' => $this->book,
            'rating' => 3,
        ]);

        $response = $this->getJson("/api/v1/books/{$this->book->id}");

        $response->assertOk();
        $response->assertJsonFragment([
            'reviews_count' => 2,
            'reviews_avg_rating' => '4.00',
        ]);
        $response->assertJsonCount(2, 'data.reviews');

    }

    public function test_guest_can_get_book_with_correct_structure(): void
    {
        $book = Book::factory()->create([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
        $book->genres()->attach($this->genre->id);
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'author',
                'genres',
                'isbn',
                'published_date',
                'description',
                'image_url',
                'reviews_avg_rating',
                'reviews_count',
                'reviews' => [
                    '*' => [
                        'user_name',
                        'rating',
                        'comment',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ],
        ]);
        $response->assertJsonFragment([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'reviews_avg_rating' => '5.00',
            'reviews_count' => 1,
        ]);
        $response->assertJsonFragment([
            'genres' => [['id' => $this->genre->id, 'name' => $this->genre->name]],
        ]);
    }

    public function test_return_404_when_book_not_found(): void
    {
        $response = $this->getJson('/api/v1/books/999');

        $response->assertStatus(404);
        $response->assertJson([
            'message' => '書籍が見つかりません',
        ]);
    }

    public function test_authenticated_user_can_store_book(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => '書籍を登録しました',
        ]);
        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
    }

    public function test_guest_cannot_store_book(): void
    {
        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => '認証が必要です',
        ]);
    }

    public function test_return_422_when_validation_fails(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => '',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'author' => '著者名を入力してください',
        ]);
    }

    public function test_owner_can_update_book(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])->putJson("/api/v1/books/{$this->book->id}", [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'genres' => [$this->genre->id],
        ]);

        $response->assertOk();
        $response->assertJson([
            'message' => '書籍を更新しました',
        ]);
        $this->assertDatabaseHas('books', [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
        ]);
    }

    public function test_returns_404_when_updating_non_existent_book(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])->putJson('/api/v1/books/999', [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'genres' => [$this->genre->id],
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => '書籍が見つかりません',
        ]);
    }

    public function test_guest_cannot_update_book(): void
    {
        $response = $this->putJson("/api/v1/books/{$this->book->id}", [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'genres' => [$this->genre->id],
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => '認証が必要です',
        ]);
    }

    public function test_non_owner_cannot_update_book(): void
    {
        $otherUser = User::factory()->create();
        $token = $otherUser->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])->putJson("/api/v1/books/{$this->book->id}", [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'genres' => [$this->genre->id],
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'この操作を行う権限がありません',
        ]);
    }

    public function test_owner_can_delete_book(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])->deleteJson("/api/v1/books/{$this->book->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('books', [
            'id' => $this->book->id,
        ]);
    }

    public function test_returns_404_when_deleting_non_existent_book(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])->deleteJson('/api/v1/books/999');

        $response->assertStatus(404);
        $response->assertJson([
            'message' => '書籍が見つかりません',
        ]);
    }

    public function test_guest_cannot_delete_book(): void
    {
        $response = $this->deleteJson("/api/v1/books/{$this->book->id}");

        $response->assertStatus(401);
        $response->assertJson([
            'message' => '認証が必要です',
        ]);
    }

    public function test_non_owner_cannot_delete_book(): void
    {
        $otherUser = User::factory()->create();
        $token = $otherUser->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])->deleteJson("/api/v1/books/{$this->book->id}");

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'この操作を行う権限がありません',
        ]);
    }
}
