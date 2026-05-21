<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_genre_belongs_to_many_books(): void
    {
        $genre = Genre::factory()->create();
        $books = Book::factory()->count(3)->create();
        $genre->books()->attach($books);

        $relatedBooks = $genre->books;

        $this->assertInstanceOf(Collection::class, $relatedBooks);
        $this->assertCount(3, $relatedBooks);
    }
}
