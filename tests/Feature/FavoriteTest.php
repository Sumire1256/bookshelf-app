<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
    }

    public function test_authenticated_user_can_add_favorite(): void
    {
        $response = $this->actingAs($this->user)->from(route('books.show', $this->book))->post(route('favorites.toggle', $this->book));

        $response->assertRedirect(route('books.show', $this->book));
        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_authenticated_user_can_remove_favorite(): void
    {
        $this->user->favoriteBooks()->attach($this->book->id);

        $response = $this->actingAs($this->user)->from(route('books.show', $this->book))->post(route('favorites.toggle', $this->book));

        $response->assertRedirect(route('books.show', $this->book));
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_authenticated_user_can_re_add_favorite(): void
    {
        $this->user->favoriteBooks()->attach($this->book->id);
        $this->user->favoriteBooks()->detach($this->book->id);

        $response = $this->actingAs($this->user)->from(route('books.show', $this->book))->post(route('favorites.toggle', $this->book));

        $response->assertRedirect(route('books.show', $this->book));
        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_guest_can_not_add_favorite(): void
    {
        $response = $this->post(route('favorites.toggle', $this->book));

        $response->assertRedirect(route('login'));
    }

    public function test_favorite_index_shows_user_favorite_books(): void
    {
        $this->user->favoriteBooks()->attach($this->book->id);

        $response = $this->actingAs($this->user)->get(route('favorites.index'));

        $response->assertOk();
        $response->assertSee($this->book->title);
    }

    // お気に入り一覧に他人のお気に入りは表示されない
    public function test_favorite_index_does_not_show_other_users_favorites(): void
    {
        $otherUser = User::factory()->create();
        $otherBook = Book::factory()->create();
        $otherUser->favoriteBooks()->attach($otherBook->id);

        $response = $this->actingAs($this->user)->get(route('favorites.index'));

        $response->assertOk();
        $response->assertDontSee($otherBook->title);
    }
}
