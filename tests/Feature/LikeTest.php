<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $book;

    private $review;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
        $this->review = Review::factory()->create([
            'book_id' => $this->book->id,
        ]);
    }

    public function test_authenticated_user_can_give_a_like(): void
    {
        $response = $this->actingAs($this->user)->from(route('books.show', $this->book))->post(route('reviews.like', $this->review));

        $response->assertRedirect(route('books.show', $this->book));
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $this->user->id,
            'review_id' => $this->review->id,
        ]);
    }

    public function test_authenticated_user_can_remove_a_like(): void
    {
        $this->user->likedReviews()->attach($this->review->id);

        $response = $this->actingAs($this->user)->from(route('books.show', $this->book))->post(route('reviews.like', $this->review));

        $response->assertRedirect(route('books.show', $this->book));
        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $this->user->id,
            'review_id' => $this->review->id,
        ]);
    }

    public function test_authenticated_user_can_re_give_a_like(): void
    {
        $this->user->likedReviews()->attach($this->review->id);
        $this->user->likedReviews()->detach($this->review->id);

        $response = $this->actingAs($this->user)->from(route('books.show', $this->book))->post(route('reviews.like', $this->review));

        $response->assertRedirect(route('books.show', $this->book));
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $this->user->id,
            'review_id' => $this->review->id,
        ]);
    }

    public function test_guest_can_not_give_a_like(): void
    {
        $response = $this->post(route('reviews.like', $this->review));

        $response->assertRedirect(route('login'));
    }
}
