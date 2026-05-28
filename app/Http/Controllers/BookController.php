<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Services\BookSearchService;
use App\Services\GoogleBooksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * 書籍の一覧を表示する
     * キーワード・ジャンル・並び順での絞り込みに対応
     *
     * @param  Request  $request  キーワード・ジャンル・並び順を含むリクエスト
     * @param  BookSearchService  $bookSearchService  書籍検索サービス
     */
    public function index(Request $request, BookSearchService $bookSearchService): View
    {
        $books = $bookSearchService->getFilteredBooks($request)->appends($request->query());

        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍の登録画面を表示する
     */
    public function create(): View
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を保存する
     *
     * @param  StoreBookRequest  $request  バリデーション済みのリクエスト
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $book = auth()->user()->books()->create($request->safe()->except('genres'));
        $book->genres()->attach($request->input('genres'));

        return redirect()->route('books.show', $book)->with('success', '書籍を登録しました');
    }

    /**
     * 書籍の詳細を表示する
     *
     * @param  Book  $book  表示対象の書籍
     */
    public function show(Book $book): View
    {
        $book = $book->load('genres', 'reviews.user');

        return view('books.show', compact('book'));
    }

    /**
     * 書籍の編集画面を表示する
     *
     * @param  Book  $book  編集対象の書籍
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍を更新する
     *
     * @param  UpdateBookRequest  $request  バリデーション済みのリクエスト
     * @param  Book  $book  更新対象の書籍
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $book->update($request->safe()->except('genres'));
        $book->genres()->sync($request->input('genres'));

        return redirect()->route('books.show', $book)->with('success', '書籍を更新しました');
    }

    /**
     * 書籍を削除する
     *
     * @param  Book  $book  削除対象の書籍
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $book->delete();

        return redirect()->route('books.index')->with('success', '書籍を削除しました');
    }

    /**
     * ISBNから書籍情報を取得する
     *
     * @param  string  $isbn  検索するISBN
     * @param  GoogleBooksService  $googleBooksService  GoogleBooksAPIサービス
     */
    public function fetchByIsbn(string $isbn, GoogleBooksService $googleBooksService): JsonResponse
    {
        if (strlen($isbn) !== 13 || ! ctype_digit($isbn)) {
            return response()->json([
                'error' => 'ISBNは13桁の数字で入力してください',
            ], 422);
        }

        try {
            $bookInfo = $googleBooksService->fetchByIsbn($isbn);

            if (is_null($bookInfo)) {
                return response()->json([
                    'error' => '書籍が見つかりませんでした',
                ], 404);
            }

            return response()->json($bookInfo, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
