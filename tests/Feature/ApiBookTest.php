<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiBookTest extends TestCase
{
    use RefreshDatabase;

    private $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = Genre::factory()->create();
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
            'genres' => [$this->genre->name],
        ]);
    }
}
