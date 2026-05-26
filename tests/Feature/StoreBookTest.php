<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreBookTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->genre = Genre::factory()->create();
    }

    // 書籍登録の許可
    public function test_authenticated_user_can_store_book(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
        $response->assertRedirect(route('books.show', Book::first()));
        $response->assertSessionHas('success', '書籍を登録しました');
    }

    public function test_guest_can_not_store_book(): void
    {
        $response = $this->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $response->assertRedirect(route('login'));
    }

    // 書籍登録時のバリデーション
    public function test_validation_fails_when_title_is_empty(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => '',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['title']);
        $this->assertContains('タイトルを入力してください', session()->get('errors')->get('title'));
    }

    public function test_book_title_within_255_characters_is_valid(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => str_repeat('あ', 255),
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => str_repeat('あ', 255),
            'author' => 'テスト著者',
        ]);
    }

    public function test_validation_fails_when_title_is_too_long(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => str_repeat('あ', 256),
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['title']);
        $this->assertContains('タイトルは255文字以内で入力してください', session()->get('errors')->get('title'));
    }

    public function test_validation_fails_when_author_is_empty(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['author']);
        $this->assertContains('著者名を入力してください', session()->get('errors')->get('author'));
    }

    public function test_author_within_50_characters_is_valid(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => str_repeat('あ', 50),
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => str_repeat('あ', 50),
        ]);
    }

    public function test_validation_fails_when_author_is_too_long(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => str_repeat('あ', 51),
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['author']);
        $this->assertContains('著者名は50文字以内で入力してください', session()->get('errors')->get('author'));
    }

    public function test_book_can_be_stored_without_isbn(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
    }

    public function test_validation_fails_when_isbn_is_less_than_13_digits(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '978410101001',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['isbn']);
        $this->assertContains('ISBNコードは13桁で入力してください', session()->get('errors')->get('isbn'));
    }

    public function test_validation_fails_when_isbn_is_more_than_13_digits(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '97841010100140',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['isbn']);
        $this->assertContains('ISBNコードは13桁で入力してください', session()->get('errors')->get('isbn'));
    }

    public function test_validation_fails_when_isbn_is_already_stored(): void
    {
        $book = Book::factory()->create([
            'isbn' => '9784101010014',
        ]);

        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['isbn']);
        $this->assertContains('この書籍は既に登録されています', session()->get('errors')->get('isbn'));
    }

    public function test_book_can_be_stored_without_published_date(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '',
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
    }

    public function test_validation_fails_when_published_date_is_future(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => now()->addDay()->format('Y-m-d'),
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['published_date']);
        $this->assertContains('出版日は今日以前の日付を入力してください', session()->get('errors')->get('published_date'));
    }

    public function test_book_can_be_stored_with_today_as_published_date(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => now()->format('Y-m-d'),
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
    }

    public function test_book_can_be_stored_with_past_published_date(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => now()->subDay()->format('Y-m-d'),
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
    }

    public function test_description_within_10000_characters_is_valid(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'description' => str_repeat('あ', 10000),
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
    }

    public function test_validation_fails_when_description_is_too_long(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'description' => str_repeat('あ', 10001),
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['description']);
        $this->assertContains('説明は10000文字以内で入力してください', session()->get('errors')->get('description'));
    }

    public function test_book_can_be_stored_with_valid_image_url(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
    }

    public function test_validation_fails_when_image_url_is_invalid(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'image_url' => 'あいうえお',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['image_url']);
        $this->assertContains('画像URLは有効なURLで入力してください', session()->get('errors')->get('image_url'));
    }

    public function test_validation_fails_when_image_url_exceeds_255_characters(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'image_url' => 'https://example.com/'.str_repeat('a', 240),
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['image_url']);
        $this->assertContains('画像URLは255文字以内で入力してください', session()->get('errors')->get('image_url'));
    }

    public function test_validation_fails_when_genre_is_empty(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'genres' => [],
        ]);

        $response->assertSessionHasErrors(['genres']);
        $this->assertContains('ジャンルは１つ以上選択してください', session()->get('errors')->get('genres'));
    }

    public function test_validation_fails_when_genre_does_not_exist(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2024-01-01',
            'genres' => [9999],
        ]);

        $response->assertSessionHasErrors('genres.0');
        $this->assertContains('選択されたジャンルは存在しません', session()->get('errors')->get('genres.0'));
    }
}
