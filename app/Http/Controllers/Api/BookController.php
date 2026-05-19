<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookCollectionResource;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    /**
     * 書籍の一覧を取得する
     */
    public function index(): AnonymousResourceCollection
    {
        $books = Book::with('genres')->withAvg('reviews', 'rating')->withCount('reviews')->latest()->paginate(10);

        return BookCollectionResource::collection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * 書籍の詳細を取得する
     */
    public function show(Book $book): BookResource
    {
        $book->load(['genres', 'reviews' => fn ($query) => $query->latest(), 'reviews.user'])->loadAvg('reviews', 'rating')->loadCount('reviews');

        return new BookResource($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
