<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_without_review_is_not_shown_in_ranking(): void
    {
        $book = Book::factory()->create();

        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertDontSee($book->title);
        $response->assertSee('まだレビューが投稿された書籍がありません。');
    }

    public function test_book_with_review_is_shown_in_ranking(): void
    {
        $book = Book::factory()->create();
        Review::factory()->create([
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertSee($book->title);
    }

    public function test_books_are_displayed_in_descending_order_of_average_rating(): void
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

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        // 高評価書籍が低評価書籍より前に表示される
        $content = $response->getContent();
        $this->assertGreaterThan(
            strpos($content, $highRatedBook->title),
            strpos($content, $lowRatedBook->title)
        );
    }

    public function test_ranking_shows_top_10_books(): void
    {
        $books = Book::factory()->count(15)->create();
        $books->each(function ($book) {
            Review::factory()->create([
                'book_id' => $book->id,
                'rating' => rand(1, 5),
            ]);
        });

        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertViewHas('rankedBooks', function ($rankedBooks) {
            return $rankedBooks->count() <= 10;
        });
    }

    public function test_ranking_displays_correctly_with_same_rating(): void
    {
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        Review::factory()->create(['book_id' => $book1->id, 'rating' => 5]);
        Review::factory()->create(['book_id' => $book2->id, 'rating' => 5]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertSee($book1->title);
        $response->assertSee($book2->title);
    }
}
