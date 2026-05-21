<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_belongs_to_book(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);

        $reviewedBook = $review->book;

        $this->assertInstanceOf(Book::class, $reviewedBook);
        $this->assertEquals($book->id, $reviewedBook->id);
    }

    public function test_review_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $reviewUser = $review->user;

        $this->assertInstanceOf(User::class, $reviewUser);
        $this->assertEquals($user->id, $reviewUser->id);
    }

    public function test_review_belongs_to_many_liked_by_users(): void
    {
        $users = User::factory()->count(3)->create();
        $review = Review::factory()->create();
        $review->likedByUsers()->attach($users);

        $likes = $review->likedByUsers;

        $this->assertInstanceOf(Collection::class, $likes);
        $this->assertCount(3, $likes);
    }
}
