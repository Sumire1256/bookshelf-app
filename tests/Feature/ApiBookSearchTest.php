<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiBookSearchTest extends TestCase
{
    use RefreshDatabase;

    private $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = Genre::factory()->create([
            'name' => '技術書',
        ]);
    }

    public function test_user_can_get_books_with_pagination_in_latest_order(): void
    {
        Book::factory()->count(11)->create();

        $firstPage = $this->getJson('/api/v1/books');

        $firstPage->assertOk();
        $firstPage->assertJsonCount(10, 'data');
        $firstPage->assertJsonStructure(['links', 'meta']);
        $firstPage->assertJsonPath('data.0.id', Book::latest()->first()->id);

        $secondPage = $this->getJson('/api/v1/books?page=2');

        $secondPage->assertOk();
        $secondPage->assertJsonCount(1, 'data');
    }

    public function test_books_are_filtered_by_keyword_title(): void
    {
        $book = Book::factory()->create([
            'title' => 'Laravel',
            'author' => '山田太郎',
        ]);
        $otherBook = Book::factory()->create([
            'title' => 'Ruby',
        ]);

        $response = $this->getJson('/api/v1/books?keyword=Laravel');

        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'title' => 'Laravel',
        ]);
    }

    public function test_books_are_filtered_by_keyword_author(): void
    {
        $book = Book::factory()->create([
            'title' => 'Laravel',
            'author' => '山田太郎',
        ]);
        $otherBook = Book::factory()->create([
            'title' => 'Laravel',
            'author' => '佐藤健太',
        ]);

        $response = $this->getJson('/api/v1/books?keyword=山田');

        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'author' => '山田太郎',
        ]);
    }

    public function test_books_are_filtered_by_genre(): void
    {
        $book = Book::factory()->create([
            'title' => 'Laravel',
        ]);
        $book->genres()->attach($this->genre->id);

        $otherBook = Book::factory()->create(['title' => 'Ruby']);
        $otherGenre = Genre::factory()->create();
        $otherBook->genres()->attach($otherGenre->id);

        $response = $this->getJson("/api/v1/books?genre={$this->genre->id}");

        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'title' => 'Laravel',
        ]);
    }

    public function test_books_are_filtered_by_keyword_and_genre(): void
    {
        $book = Book::factory()->create([
            'title' => 'Laravel',
            'author' => '山田太郎',
        ]);
        $book->genres()->attach($this->genre->id);

        $book2 = Book::factory()->create([
            'title' => 'C#',
            'author' => '佐藤健太',
        ]);
        $book2->genres()->attach($this->genre->id);

        $book3 = Book::factory()->create([
            'title' => 'エンジニア',
            'author' => '山田太郎',
        ]);
        $otherGenre = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);
        $book3->genres()->attach($otherGenre->id);

        $response = $this->getJson("/api/v1/books?keyword=山田&genre={$this->genre->id}");

        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'title' => 'Laravel',
            'author' => '山田太郎',
            'genres' => ['技術書'],
        ]);
    }

    public function test_books_are_sorted_by_newest(): void
    {
        $oldBook = Book::factory()->create(['created_at' => now()->subDay()]);
        $newBook = Book::factory()->create(['created_at' => now()]);

        $response = $this->getJson('/api/v1/books?sort=newest');

        $response->assertJsonPath('data.0.id', $newBook->id);
        $response->assertJsonPath('data.1.id', $oldBook->id);
    }

    public function test_books_are_sorted_by_oldest(): void
    {
        $oldBook = Book::factory()->create(['created_at' => now()->subDay()]);
        $newBook = Book::factory()->create(['created_at' => now()]);

        $response = $this->getJson('/api/v1/books?sort=oldest');

        $response->assertJsonPath('data.0.id', $oldBook->id);
        $response->assertJsonPath('data.1.id', $newBook->id);
    }

    public function test_books_are_sorted_by_title(): void
    {
        $bookA = Book::factory()->create(['title' => 'AAA']);
        $bookZ = Book::factory()->create(['title' => 'ZZZ']);

        $response = $this->getJson('/api/v1/books?sort=title');

        $response->assertJsonPath('data.0.id', $bookA->id);
        $response->assertJsonPath('data.1.id', $bookZ->id);
    }

    public function test_books_are_sorted_by_rating(): void
    {
        $lowRatedBook = Book::factory()->create();
        Review::factory()->create([
            'book_id' => $lowRatedBook->id,
            'rating' => 2,
        ]);

        $highRatedBook = Book::factory()->create();
        Review::factory()->create([
            'book_id' => $highRatedBook->id,
            'rating' => 5,
        ]);

        $response = $this->getJson('/api/v1/books?sort=rating');

        $response->assertJsonPath('data.0.id', $highRatedBook->id);
        $response->assertJsonPath('data.1.id', $lowRatedBook->id);
    }

    public function test_books_are_sorted_by_default_when_invalid_sort_given(): void
    {
        $oldBook = Book::factory()->create(['created_at' => now()->subDay()]);
        $newBook = Book::factory()->create(['created_at' => now()]);

        $response = $this->getJson('/api/v1/books?sort=invalid');

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $newBook->id);
        $response->assertJsonPath('data.1.id', $oldBook->id);
    }

    public function test_search_conditions_are_maintained_on_pagination(): void
    {
        Book::factory()->count(11)->create(['author' => '山田太郎'])
            ->each(fn ($book) => $book->genres()->attach($this->genre->id));

        $params = [
            'keyword' => '山田',
            'genre' => $this->genre->id,
            'sort' => 'newest',
        ];

        // 1ページ目
        $firstPage = $this->getJson('/api/v1/books?'.http_build_query($params));
        $firstPage->assertOk();
        $firstPage->assertJsonCount(10, 'data');

        // 2ページ目でも検索条件が維持される
        $secondPage = $this->getJson('/api/v1/books?'.http_build_query(
            array_merge($params, ['page' => 2])
        ));
        $secondPage->assertOk();
        $secondPage->assertJsonCount(1, 'data');
    }
}
