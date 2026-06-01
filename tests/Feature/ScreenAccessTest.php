<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScreenAccessTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guest_can_access_books_index(): void
    {
        $response = $this->get(route('books.index'));

        $response->assertOk();
        $response->assertViewIs('books.index');
    }

    public function test_guest_can_access_book_show(): void
    {
        $book = Book::factory()->create();
        $response = $this->get(route('books.show', $book));

        $response->assertOk();
        $response->assertViewIs('books.show');
    }

    public function test_guest_can_access_ranking(): void
    {
        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertViewIs('ranking.index');
    }

    public function test_guest_is_redirected_to_login_when_accessing_book_create(): void
    {
        $response = $this->get(route('books.create'));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_accessing_favorite_index(): void
    {
        $response = $this->get(route('favorites.index'));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_accessing_genres_index(): void
    {
        $response = $this->get(route('genres.index'));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_accessing_genre_show(): void
    {
        $genre = Genre::factory()->create();
        $response = $this->get(route('genres.show', $genre));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_accessing_genre_create(): void
    {
        $response = $this->get(route('genres.create'));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_accessing_genre_edit(): void
    {
        $genre = Genre::factory()->create();
        $response = $this->get(route('genres.edit', $genre));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_accessing_review_edit(): void
    {
        $review = Review::factory()->create();
        $response = $this->get(route('reviews.edit', $review));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_book_create(): void
    {
        $response = $this->actingAs($this->user)->get(route('books.create'));

        $response->assertOk();
        $response->assertViewIs('books.create');
    }

    public function test_authenticated_user_can_access_favorite_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('favorites.index'));

        $response->assertOk();
        $response->assertViewIs('favorites.index');
    }

    public function test_authenticated_user_can_access_genres_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('genres.index'));

        $response->assertOk();
        $response->assertViewIs('genres.index');
    }

    public function test_authenticated_user_access_genre_show(): void
    {
        $genre = Genre::factory()->create();
        $response = $this->actingAs($this->user)->get(route('genres.show', $genre));

        $response->assertOk();
        $response->assertViewIs('genres.show');
    }

    public function test_authenticated_user_can_access_genre_create(): void
    {
        $response = $this->actingAs($this->user)->get(route('genres.create'));

        $response->assertOk();
        $response->assertViewIs('genres.create');
    }

    public function test_authenticated_user_can_access_genre_edit(): void
    {
        $genre = Genre::factory()->create();
        $response = $this->actingAs($this->user)->get(route('genres.edit', $genre));

        $response->assertOk();
        $response->assertViewIs('genres.edit');
    }

    public function test_owner_can_access_review_edit(): void
    {
        $review = Review::factory()->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->user)->get(route('reviews.edit', $review));

        $response->assertOk();
        $response->assertViewIs('reviews.edit');
    }

    public function test_owner_can_access_book_edit(): void
    {
        $book = Book::factory()->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->user)->get(route('books.edit', $book));

        $response->assertOk();
        $response->assertViewIs('books.edit');
    }

    public function test_non_owner_can_not_access_review_edit(): void
    {
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($otherUser)->get(route('reviews.edit', $review));

        $response->assertForbidden();
        $response->assertSee('アクセス権限がありません');
    }

    public function test_non_owner_can_not_access_book_edit(): void
    {
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($otherUser)->get(route('books.edit', $book));

        $response->assertForbidden();
        $response->assertSee('アクセス権限がありません');
    }

    public function test_authenticated_user_can_access_my_book_reports(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewIs('reports.index');
    }

    public function test_guest_is_redirected_to_login_when_accessing_my_book_reports(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_not_found_page_is_displayed_when_accessing_non_existent_book(): void
    {
        $response = $this->get('/books/9999');

        $response->assertNotFound();
        $response->assertSee('ページが見つかりません');
    }
}
