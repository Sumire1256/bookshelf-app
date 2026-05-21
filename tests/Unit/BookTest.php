<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    private $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->book = Book::factory()->create();
    }

    public function test_book_has_many_reviews(): void
    {
        Review::factory()->count(3)->create(['book_id' => $this->book->id]);

        $reviews = $this->book->reviews;

        $this->assertInstanceOf(Collection::class, $reviews);
        $this->assertCount(3, $reviews);
    }

    public function test_book_belongs_to_many_genres(): void
    {
        $genres = Genre::factory()->count(3)->create();
        $this->book->genres()->attach($genres);

        $bookGenres = $this->book->genres;

        $this->assertInstanceOf(Collection::class, $bookGenres);
        $this->assertCount(3, $bookGenres);
    }

    public function test_book_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $bookUser = $book->user;

        $this->assertInstanceOf(User::class, $bookUser);
        $this->assertEquals($user->id, $bookUser->id);
    }
}
