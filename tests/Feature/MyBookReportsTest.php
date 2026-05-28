<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyBookReportsTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_total_reviews_count_is_correct(): void
    {
        Review::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 3;
        });
    }

    public function test_books_read_count_is_correct(): void
    {
        Review::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['books_read'] === 3;
        });
    }

    public function test_average_rating_is_correct(): void
    {
        collect([1, 3, 5])->each(fn ($rating) => Review::factory()->create([
            'user_id' => $this->user,
            'rating' => $rating,
        ]));

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['average_rating'] == 3.0;
        });
    }

    public function test_rating_distribution_is_correct(): void
    {
        $ratingCounts = [1 => 1, 2 => 2, 3 => 0, 4 => 3, 5 => 1];

        collect($ratingCounts)->each(function ($count, $rating) {
            Review::factory()->count($count)->create([
                'user_id' => $this->user->id,
                'rating' => $rating,
            ]);
        });

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) use ($ratingCounts) {
            return collect($ratingCounts)->values()->every(
                fn ($count, $index) => $stats['rating_distribution'][$index] === $count
            );
        });
    }

    public function test_top_rated_books_shows_all_when_less_than_5(): void
    {
        collect([4, 5, 5])->each(fn ($rating) => Review::factory()->create([
            'user_id' => $this->user->id,
            'rating' => $rating,
        ]));

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            return count($stats['top_rated_books']) === 3;
        });
    }

    public function test_top_rated_books_shows_only_5_when_more_than_5(): void
    {
        collect([4, 4, 4, 5, 5, 5])->each(fn ($rating) => Review::factory()->create([
            'user_id' => $this->user->id,
            'rating' => $rating,
        ]));

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            $books = collect($stats['top_rated_books']);

            if ($books->count() !== 5) {
                return false;
            }

            $ratings = $books->pluck('rating');

            return $ratings->values()->every(function ($rating, $index) use ($ratings) {
                if ($index === 0) {
                    return true;
                }

                return $rating <= $ratings->values()[$index - 1];
            });
        });
    }

    public function test_genre_ratings_shows_all_when_less_than_5(): void
    {
        Genre::factory()->count(3)->create()->each(function ($genre) {
            $book = Book::factory()->create();
            $book->genres()->attach($genre->id);
            Review::factory()->create([
                'user_id' => $this->user->id,
                'book_id' => $book->id,
                'rating' => rand(1, 5),
            ]);
        });

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            return count($stats['genre_ratings']) === 3;
        });
    }

    public function test_genre_ratings_shows_only_5_when_more_than_5(): void
    {
        Genre::factory()->count(6)->create()->each(function ($genre) {
            $book = Book::factory()->create();
            $book->genres()->attach($genre->id);
            Review::factory()->create([
                'user_id' => $this->user->id,
                'book_id' => $book->id,
                'rating' => rand(1, 5),
            ]);
        });

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            $genres = $stats['genre_ratings'];

            if ($genres->count() !== 5) {
                return false;
            }

            $ratings = $genres->pluck('average_rating');

            return $ratings->values()->every(function ($rating, $index) use ($ratings) {
                if ($index === 0) {
                    return true;
                }

                return $rating <= $ratings->values()[$index - 1];
            });
        });
    }

    public function test_all_stats_are_empty_when_no_reviews(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 0
                && $stats['summary']['books_read'] === 0
                && $stats['summary']['average_rating'] === 0
                && $stats['rating_distribution']->sum() === 0
                && count($stats['top_rated_books']) === 0
                && count($stats['genre_ratings']) === 0;
        });
    }

    public function test_other_users_reviews_are_not_included_in_stats(): void
    {
        $otherUser = User::factory()->create();
        Review::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 0
                && $stats['summary']['books_read'] === 0
                && $stats['summary']['average_rating'] == 0
                && $stats['rating_distribution']->sum() === 0
                && count($stats['top_rated_books']) === 0
                && count($stats['genre_ratings']) === 0;
        });
    }

    public function test_books_with_rating_below_4_are_not_included_in_top_rated(): void
    {
        collect([1, 2, 3, 4, 5])->each(fn ($rating) => Review::factory()->create([
            'user_id' => $this->user->id,
            'rating' => $rating,
        ]));

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            return count($stats['top_rated_books']) === 2;
        });
    }

    public function test_reviews_across_multiple_books_in_same_are_aggregated_correctly(): void
    {
        $genre = Genre::factory()->create();
        collect([1, 3, 5])->each(function ($rating) use ($genre) {
            $book = Book::factory()->create();
            $book->genres()->attach($genre->id);
            Review::factory()->create([
                'user_id' => $this->user->id,
                'book_id' => $book->id,
                'rating' => $rating,
            ]);
        });

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            $genreRating = collect($stats['genre_ratings'])->first();

            return $genreRating['count'] === 3
                && $genreRating['average_rating'] == 3.0;
        });
    }
}
