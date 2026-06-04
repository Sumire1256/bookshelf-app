<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ジャンル登録の許可
    public function test_authenticated_user_can_store_genre(): void
    {
        $response = $this->actingAs($this->user)->post(route('genres.store'), [
            'name' => 'テスト',
        ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを登録しました');
        $this->assertDatabaseHas('genres', [
            'name' => 'テスト',
        ]);
    }

    public function test_guest_can_not_store_genre(): void
    {
        $response = $this->post(route('genres.store'), [
            'name' => 'テスト',
        ]);

        $response->assertRedirect(route('login'));
    }

    // ジャンル登録のバリデーション
    public function test_store_validation_fails_when_name_id_empty(): void
    {
        $response = $this->actingAs($this->user)->post(route('genres.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'ジャンル名を入力してください',
        ]);
    }

    public function test_store_validation_fails_when_name_is_too_long(): void
    {
        $response = $this->actingAs($this->user)->post(route('genres.store'), [
            'name' => str_repeat('あ', 51),
        ]);

        $response->assertSessionHasErrors([
            'name' => 'ジャンル名は50文字以内で入力してください',
        ]);
    }

    public function test_store_name_within_50_characters_is_valid(): void
    {
        $response = $this->actingAs($this->user)->post(route('genres.store'), [
            'name' => str_repeat('あ', 50),
        ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを登録しました');
        $this->assertDatabaseHas('genres', [
            'name' => str_repeat('あ', 50),
        ]);
    }

    public function test_user_can_not_store_duplicate_genre(): void
    {
        Genre::factory()->create([
            'name' => 'テスト',
        ]);

        $response = $this->actingAs($this->user)->post(route('genres.store'), [
            'name' => 'テスト',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'このジャンルは既に登録されています',
        ]);
    }

    // ジャンル編集の許可
    public function test_authenticated_user_can_update_genre(): void
    {
        $genre = Genre::factory()->create([
            'name' => 'テスト',
        ]);

        $response = $this->actingAs($this->user)->put(route('genres.update', $genre), [
            'name' => '更新後のジャンル',
        ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを更新しました');
        $this->assertDatabaseHas('genres', [
            'name' => '更新後のジャンル',
        ]);
    }

    public function test_guest_can_not_update_genre(): void
    {
        $genre = Genre::factory()->create([
            'name' => 'テスト',
        ]);

        $response = $this->put(route('genres.update', $genre), [
            'name' => '更新後のジャンル',
        ]);

        $response->assertRedirect(route('login'));
    }

    // ジャンル編集のバリデーション
    public function test_update_validation_fails_when_name_id_empty(): void
    {
        $genre = Genre::factory()->create([
            'name' => 'テスト',
        ]);

        $response = $this->actingAs($this->user)->put(route('genres.update', $genre), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'ジャンル名を入力してください',
        ]);
    }

    public function test_update_validation_fails_when_name_is_too_long(): void
    {
        $genre = Genre::factory()->create([
            'name' => 'テスト',
        ]);

        $response = $this->actingAs($this->user)->put(route('genres.update', $genre), [
            'name' => str_repeat('あ', 51),
        ]);

        $response->assertSessionHasErrors([
            'name' => 'ジャンル名は50文字以内で入力してください',
        ]);
    }

    public function test_update_name_within_50_characters_is_valid(): void
    {
        $genre = Genre::factory()->create([
            'name' => 'テスト',
        ]);

        $response = $this->actingAs($this->user)->put(route('genres.update', $genre), [
            'name' => str_repeat('あ', 50),
        ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを更新しました');
        $this->assertDatabaseHas('genres', [
            'name' => str_repeat('あ', 50),
        ]);
    }

    public function test_user_can_not_update_duplicate_genre(): void
    {
        $genre1 = Genre::factory()->create([
            'name' => 'テスト',
        ]);
        $genre2 = Genre::factory()->create([
            'name' => 'ジャンル',
        ]);

        $response = $this->actingAs($this->user)->put(route('genres.update', $genre1), [
            'name' => 'ジャンル',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'このジャンルは既に登録されています',
        ]);
    }

    public function test_user_can_update_genre_with_same_name(): void
    {
        $genre = Genre::factory()->create([
            'name' => 'テスト',
        ]);

        $response = $this->actingAs($this->user)->put(route('genres.update', $genre), [
            'name' => 'テスト',
        ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを更新しました');
        $this->assertDatabaseHas('genres', [
            'name' => 'テスト',
        ]);
    }

    // ジャンル削除の許可
    public function test_authenticated_user_can_destroy_genre(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを削除しました');
        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_guest_can_not_destroy_genre(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('login'));
    }

    public function test_genre_can_not_be_deleted_when_books_are_associated(): void
    {
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($this->user)->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('error', 'このジャンルは書籍が登録されているため削除できません');
    }

    // ジャンル詳細のページネーション
    public function test_get_books_by_genre_with_pagination(): void
    {
        $genre = Genre::factory()->create();
        $books = Book::factory()->count(15)->create()->each(function ($book) use ($genre) {
            $book->genres()->attach($genre->id);
        });

        $firstPage = $this->actingAs($this->user)->get(route('genres.show', $genre));

        $firstPage->assertOk();
        $this->assertCount(10, $firstPage->viewData('books'));
        $firstPage->assertSee('?page=2');

        $secondPage = $this->actingAs($this->user)->get(route('genres.show', $genre).'?page=2');

        $secondPage->assertOk();
        $this->assertCount(5, $secondPage->viewData('books'));
    }
}
