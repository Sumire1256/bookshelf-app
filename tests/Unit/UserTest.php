<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_has_many_books(): void
    {
        Book::factory()->count(3)->create(['user_id' => $this->user->id]);

        $books = $this->user->books;

        $this->assertInstanceOf(Collection::class, $books);
        $this->assertCount(3, $books);
    }

    public function test_user_has_many_reviews(): void
    {
        Review::factory()->count(3)->create(['user_id' => $this->user->id]);

        $reviews = $this->user->reviews;

        $this->assertInstanceOf(Collection::class, $reviews);
        $this->assertCount(3, $reviews);
    }

    public function test_user_belongs_to_many_favorites_books(): void
    {
        $books = Book::factory()->count(3)->create();
        $this->user->favoriteBooks()->attach($books);

        $favoriteBooks = $this->user->favoriteBooks;

        $this->assertInstanceOf(Collection::class, $favoriteBooks);
        $this->assertCount(3, $favoriteBooks);
    }

    public function test_user_belongs_to_many_liked_reviews(): void
    {
        $reviews = Review::factory()->count(3)->create();
        $this->user->likedReviews()->attach($reviews);

        $likedReviews = $this->user->likedReviews;

        $this->assertInstanceOf(Collection::class, $likedReviews);
        $this->assertCount(3, $likedReviews);
    }
}
