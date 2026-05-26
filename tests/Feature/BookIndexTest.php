<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookIndexTest extends TestCase
{
    use RefreshDatabase;

    private $book;

    private $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->book = Book::factory()->create([
            'title' => 'Laravel',
            'author' => '山田太郎',
        ]);
        $this->genre = Genre::factory()->create([
            'name' => '技術書',
        ]);
        $this->book->genres()->attach($this->genre->id);
    }

    public function test_books_index_displays_books_with_pagination_in_latest_order(): void
    {
        Book::factory()->count(10)->create();

        $firstPage = $this->get(route('books.index'));

        $firstPage->assertOk();
        $firstPage->assertViewHas('books', function ($paginatedBooks) {
            return $paginatedBooks->count() === 10;
        });
        $firstPage->assertSee('?page=2');

        // 最新順（最後に作成した書籍が1ページ目に表示）
        $firstPage->assertViewHas('books', function ($paginatedBooks) {
            $expectedFirstId = Book::latest()->first()->id;

            return $paginatedBooks->first()->id === $expectedFirstId;
        });

        $secondPage = $this->get(route('books.index').'?page=2');

        $secondPage->assertOk();
        $secondPage->assertViewHas('books', function ($books) {
            return $books->count() === 1;
        });
    }

    public function test_books_are_filtered_by_keyword_title(): void
    {
        $otherBook = Book::factory()->create([
            'title' => 'Ruby',
        ]);

        $response = $this->get(route('books.index', ['keyword' => 'Laravel']));

        $response->assertSee('Laravel');
        $response->assertDontSee('Ruby');
    }

    public function test_books_are_filtered_by_keyword_author(): void
    {
        $otherBook = Book::factory()->create([
            'title' => 'C#',
            'author' => '佐藤健太',
        ]);

        $response = $this->get(route('books.index', ['keyword' => '山田']));

        $response->assertSee('山田太郎');
        $response->assertDontSee('佐藤健太');
    }

    public function test_books_are_filtered_by_genre(): void
    {
        $otherBook = Book::factory()->create();
        $otherGenre = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);
        $otherBook->genres()->attach($otherGenre->id);

        $response = $this->get(route('books.index', ['genre' => $this->genre->id]));

        $response->assertSee($this->book->title);
        $response->assertDontSee($otherBook->title);
    }

    public function test_books_are_filtered_by_keyword_and_genre(): void
    {
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

        $response = $this->get(route('books.index', ['keyword' => '山田', 'genre' => $this->genre->id]));

        $response->assertSee($this->book->title);
        $response->assertDontSee($book2->title);
        $response->assertDontSee($book3->title);
    }

    public function test_books_are_sorted_by_newest(): void
    {
        $oldBook = Book::factory()->create(['created_at' => now()->subDay()]);
        $newBook = Book::factory()->create(['created_at' => now()]);

        $response = $this->get(route('books.index', ['sort' => 'newest']));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertGreaterThan(
            strpos($content, $newBook->title),
            strpos($content, $oldBook->title)
        );
    }

    public function test_books_are_sorted_by_oldest(): void
    {
        $oldBook = Book::factory()->create(['created_at' => now()->subDay()]);
        $newBook = Book::factory()->create(['created_at' => now()]);

        $response = $this->get(route('books.index', ['sort' => 'oldest']));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertGreaterThan(
            strpos($content, $oldBook->title),
            strpos($content, $newBook->title)
        );
    }

    public function test_books_are_sorted_by_title(): void
    {
        $bookA = Book::factory()->create(['title' => 'AAA']);
        $bookZ = Book::factory()->create(['title' => 'ZZZ']);

        $response = $this->get(route('books.index', ['sort' => 'title']));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertGreaterThan(
            strpos($content, $bookA->title),
            strpos($content, $bookZ->title)
        );
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

        $response = $this->get(route('books.index', ['sort' => 'rating']));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertGreaterThan(
            strpos($content, $highRatedBook->title),
            strpos($content, $lowRatedBook->title)
        );
    }

    public function test_search_conditions_are_maintained_on_pagination(): void
    {
        Book::factory()->count(10)->create([
            'author' => '山田太郎',
        ])->each(fn ($book) => $book->genres()->attach($this->genre->id));

        // 検索条件を指定
        $params = [
            'keyword' => '山田',
            'genre' => $this->genre->id,
            'sort' => 'newest',
        ];

        $firstPage = $this->get(route('books.index', $params));
        $firstPage->assertOk();
        $firstPage->assertViewHas('books', fn ($books) => $books->count() === 10);

        // 2ページ目に検索条件を維持したまま遷移
        $secondPage = $this->get(route('books.index', array_merge($params, ['page' => 2])));
        $secondPage->assertOk();

        // 2ページ目でも検索条件が維持されている
        $secondPage->assertViewHas('books', fn ($books) => $books->count() === 1);
    }
}
