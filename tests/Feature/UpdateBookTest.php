<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateBookTest extends TestCase
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

    // 書籍更新の許可
    public function test_owner_can_update_book(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
        ]);
        $response->assertRedirect(route('books.show', $this->book));
        $response->assertSessionHas('success', '書籍を更新しました');
    }

    public function test_guest_can_not_update_book(): void
    {
        $response = $this->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'genres' => [$this->genre->id],
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_non_owner_can_not_update_book(): void
    {
        $otherUser = User::factory()->create();
        $response = $this->actingAs($otherUser)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'genres' => [$this->genre->id],
        ]);

        $response->assertForbidden();
    }

    // 書籍更新時のバリデーション
    public function test_validation_fails_when_title_is_empty(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '',
            'author' => '更新後著者',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors([
            'title' => 'タイトルを入力してください',
        ]);
    }

    public function test_book_title_within_255_characters_is_valid(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => str_repeat('あ', 255),
            'author' => '更新後著者',
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => str_repeat('あ', 255),
            'author' => '更新後著者',
        ]);
        $response->assertRedirect(route('books.show', $this->book));
    }

    public function test_validation_fails_when_title_is_too_long(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => str_repeat('あ', 256),
            'author' => '更新後著者',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors([
            'title' => 'タイトルは255文字以内で入力してください',
        ]);
    }

    public function test_validation_fails_when_author_is_empty(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors([
            'author' => '著者名を入力してください',
        ]);
    }

    public function test_author_within_50_characters_is_valid(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => str_repeat('あ', 50),
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => '更新後タイトル',
            'author' => str_repeat('あ', 50),
        ]);
        $response->assertRedirect(route('books.show', $this->book));
    }

    public function test_validation_fails_when_author_is_too_long(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => str_repeat('あ', 51),
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors([
            'author' => '著者名は50文字以内で入力してください',
        ]);
    }

    public function test_book_can_be_updated_without_isbn(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '',
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
        ]);
        $response->assertRedirect(route('books.show', $this->book));
    }

    // ISBNの一意制約のテスト
    public function test_owner_can_update_book_with_same_isbn(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => $this->book->isbn,
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
        ]);
        $response->assertRedirect(route('books.show', $this->book));
    }

    public function test_validation_fails_when_isbn_is_duplicated(): void
    {
        $otherBook = Book::factory()->create(['isbn' => '9784101010021']);

        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => $otherBook->isbn,
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors([
            'isbn' => 'この書籍は既に登録されています',
        ]);
    }

    public function test_book_can_be_updated_without_published_date(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'published_date' => '',
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
        ]);
        $response->assertRedirect(route('books.show', $this->book));
    }

    public function test_validation_fails_when_published_date_is_future(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'published_date' => now()->addDay()->format('Y-m-d'),
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors([
            'published_date' => '出版日は今日以前の日付を入力してください',
        ]);
    }

    public function test_book_can_be_updated_with_today_as_published_date(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'published_date' => now()->format('Y-m-d'),
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
        ]);
        $response->assertRedirect(route('books.show', $this->book));
    }

    public function test_book_can_be_updated_with_past_published_date(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'published_date' => now()->subDay()->format('Y-m-d'),
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
        ]);
        $response->assertRedirect(route('books.show', $this->book));
    }

    public function test_description_within_10000_characters_is_valid(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'description' => str_repeat('あ', 10000),
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
        ]);
        $response->assertRedirect(route('books.show', $this->book));
    }

    public function test_validation_fails_when_description_is_too_long(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'description' => str_repeat('あ', 10001),
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors([
            'description' => '説明は10000文字以内で入力してください',
        ]);
    }

    public function test_book_can_be_updated_with_valid_image_url(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
        ]);
        $response->assertRedirect(route('books.show', $this->book));
    }

    public function test_validation_fails_when_image_url_is_invalid(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'image_url' => 'あいうえお',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors([
            'image_url' => '画像URLは有効なURLで入力してください',
        ]);
    }

    public function test_validation_fails_when_image_url_exceeds_255_characters(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'image_url' => 'https://example.com/'.str_repeat('a', 240),
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors([
            'image_url' => '画像URLは255文字以内で入力してください',
        ]);
    }

    public function test_validation_fails_when_genre_is_empty(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'genres' => [],
        ]);

        $response->assertSessionHasErrors([
            'genres' => 'ジャンルは１つ以上選択してください',
        ]);
    }

    public function test_validation_fails_when_genre_does_not_exist(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'genres' => [9999],
        ]);

        $response->assertSessionHasErrors([
            'genres.0' => '選択されたジャンルは存在しません',
        ]);
    }
}
