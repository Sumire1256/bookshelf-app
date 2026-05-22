<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookCollectionResource;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
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
     * 書籍を登録する
     *
     * @param  StoreBookRequest  $request  バリデーション済みのリクエスト
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = auth()->user()->books()->create($request->safe()->except('genres'));
        $book->genres()->attach($request->input('genres'));

        return (new BookResource($book))->additional(['message' => '書籍を登録しました'])->response()->setStatusCode(201);
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
     * 書籍を更新する
     *
     * @param  UpdateBookRequest  $request  バリデーション済みのリクエスト
     * @param  Book  $book  更新対象の書籍
     */
    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        $this->authorize('update', $book);

        $book->update($request->safe()->except('genres'));
        $book->genres()->sync($request->input('genres'));

        return (new BookResource($book))->additional(['message' => '書籍を更新しました'])->response()->setStatusCode(200);
    }

    /**
     * 書籍を削除する
     *
     * @param  Book  $book  削除対象の書籍
     */
    public function destroy(Book $book): JsonResponse
    {
        $this->authorize('update', $book);

        $book->delete();

        return response()->json(null, 204);
    }
}
