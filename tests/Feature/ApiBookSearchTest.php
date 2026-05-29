<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiBookSearchTest extends TestCase
{
    use RefreshDatabase;

    private $book;

    private $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->book = Book::factory()->create([
            'title' => 'Laravel',
            'author' => '山田太郎',
        ]);
        $this->genre = Genre::factory()->create([
            'name' => '技術書',
        ]);
        $this->book->genres()->attach($this->genre->id);
    }
}
