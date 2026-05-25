<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
    }

    // レビュー投稿の許可
    public function test_authenticated_user_can_store_review(): void
    {
        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->book), [
            'rating' => 5,
            'comment' => 'テストコメント',
        ]);

        $response->assertRedirect(route('books.show', $this->book));
        $response->assertSessionHas('success', 'レビューを投稿しました');
        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'rating' => 5,
            'comment' => 'テストコメント',
        ]);
    }

    public function test_guest_can_not_store_review(): void
    {
        $response = $this->post(route('reviews.store', $this->book), [
            'rating' => 5,
            'comment' => 'テストコメント',
        ]);

        $response->assertRedirect(route('login'));
    }

    // レビューと書籍の一意制約
    public function test_user_can_not_store_duplicate_review(): void
    {
        Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->book), [
            'rating' => 5,
            'comment' => 'テストコメント',
        ]);

        $response->assertSessionHasErrors([
            'rating' => 'この書籍にはすでにレビューを投稿しています',
        ]);
    }

    // レビューのバリデーション
    public function test_validation_fails_when_rating_is_empty(): void
    {
        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->book), [
            'rating' => '',
            'comment' => 'テストコメント',
        ]);

        $response->assertSessionHasErrors([
            'rating' => '評価を選択してください',
        ]);
    }

    public function test_validation_fails_when_rating_is_out_of_range(): void
    {
        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->book), [
            'rating' => 6,
            'comment' => 'テストコメント',
        ]);

        $response->assertSessionHasErrors([
            'rating' => '評価は1～5で選択してください',
        ]);
    }

    public function test_validation_fails_when_comment_is_empty(): void
    {
        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->book), [
            'rating' => 5,
            'comment' => '',
        ]);

        $response->assertSessionHasErrors([
            'comment' => 'コメントを入力してください',
        ]);
    }

    public function test_comment_within_10000_characters_is_valid(): void
    {
        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->book), [
            'rating' => 5,
            'comment' => str_repeat('あ', 10000),
        ]);

        $response->assertRedirect(route('books.show', $this->book));
        $response->assertSessionHas('success', 'レビューを投稿しました');
    }

    public function test_validation_fails_when_comment_is_too_long(): void
    {
        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->book), [
            'rating' => 5,
            'comment' => str_repeat('あ', 10001),
        ]);

        $response->assertSessionHasErrors([
            'comment' => 'コメントは10000文字以内で入力してください',
        ]);
    }

    // レビュー編集の許可
    public function test_owner_can_edit_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('reviews.edit', $review));

        $response->assertOk();
        $response->assertViewIs('reviews.edit');
    }

    public function test_owner_can_update_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)->put(route('reviews.update', $review), [
            'rating' => 3,
            'comment' => '更新後のコメント',
        ]);

        $response->assertRedirect(route('books.show', $this->book));
        $response->assertSessionHas('success', 'レビューを更新しました');
        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'rating' => 3,
            'comment' => '更新後のコメント',
        ]);
    }

    public function test_non_owner_can_not_update_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->put(route('reviews.update', $review), [
            'rating' => 3,
            'comment' => '更新後のコメント',
        ]);

        $response->assertForbidden();
    }

    public function test_guest_can_not_update_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->put(route('reviews.update', $review), [
            'rating' => 3,
            'comment' => '更新後のコメント',
        ]);

        $response->assertRedirect(route('login'));
    }

    // レビュー削除の許可
    public function test_owner_can_destroy_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('books.show', $this->book));
        $response->assertSessionHas('success', 'レビューを削除しました');
        $this->assertDatabaseMissing('reviews', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_non_owner_can_not_destroy_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->delete(route('reviews.destroy', $review));

        $response->assertForbidden();
    }

    public function test_guest_can_not_destroy_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('login'));
    }
}
