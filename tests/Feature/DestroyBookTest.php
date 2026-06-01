<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyBookTest extends TestCase
{
    use RefreshDatabase;

    private $genre;

    private $user;

    private $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = Genre::factory()->create();
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->book->genres()->attach($this->genre->id);
    }

    // 書籍削除の許可
    public function test_owner_can_destroy_book(): void
    {
        $response = $this->actingAs($this->user)->delete(route('books.destroy', $this->book));

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('success', '書籍を削除しました');
        $this->assertDatabaseMissing('books', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_guest_can_not_destroy_book(): void
    {
        $response = $this->delete(route('books.destroy', $this->book));

        $response->assertRedirect(route('login'));
    }

    public function test_non_owner_can_not_destroy_book(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->delete(route('books.destroy', $this->book));

        $response->assertForbidden();
        $response->assertSee('アクセス権限がありません');
    }
}
