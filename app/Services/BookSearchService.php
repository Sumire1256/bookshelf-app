<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BookSearchService
{
    /**
     * 書籍一覧を検索・フィルタ・ソートして取得する
     *
     * @param  Request  $request  リクエスト
     * @return LengthAwarePaginator フィルタ・ソートされた書籍のページネーション結果
     */
    public function getFilteredBooks(Request $request): LengthAwarePaginator
    {
        return Book::with('genres')
            ->withAvg('reviews', 'rating')
            // キーワード検索（タイトル・著者）
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%'.$request->keyword.'%')->orWhere('author', 'like', '%'.$request->keyword.'%');
                });
            })
            // ジャンル絞り込み
            ->when($request->filled('genre'), function ($query) use ($request) {
                $query->whereHas('genres', function ($q) use ($request) {
                    $q->where('genres.id', $request->genre);
                });
            })
            // ソート
            ->when($request->filled('sort'), function ($query) use ($request) {
                match ($request->sort) {
                    'newest' => $query->latest(),
                    'oldest' => $query->oldest(),
                    'rating' => $query->orderByRaw('reviews_avg_rating IS NULL ASC') // レビューがない書籍は最後に表示
                        ->orderByDesc('reviews_avg_rating'),
                    'title' => $query->orderBy('title'),
                    default => $query->latest(),
                };
            }, function ($query) {
                $query->latest(); // デフォルトの並び順
            })
            ->paginate(10);
    }
}
