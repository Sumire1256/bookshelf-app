<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * マイ読書レポートを表示する
     *
     * @return View マイ読書レポートのビュー
     */
    public function index(): View
    {
        $reviews = auth()->user()->reviews()->with('book.genres')->get();

        return view('reports.index', [
            'stats' => [
                'summary' => $this->buildSummary($reviews),
                'rating_distribution' => $this->buildRatingDistribution($reviews),
                'top_rated_books' => $this->buildTopRatedBooks($reviews),
                'genre_ratings' => $this->buildGenreRatings($reviews),
            ],
        ]);
    }

    /**
     * 基本統計を生成する
     *
     * @param  Collection  $reviews  レビューコレクション
     * @return array 基本統計の配列
     */
    private function buildSummary(Collection $reviews): array
    {
        return [
            'total_reviews' => $reviews->count(),
            'books_read' => $reviews->pluck('book_id')->unique()->count(),
            'average_rating' => $reviews->avg('rating') ?? 0,
        ];
    }

    /**
     * 評価分布を生成する
     *
     * @param  Collection  $reviews  レビューコレクション
     * @return Collection 評価分布のコレクション
     */
    private function buildRatingDistribution(Collection $reviews): Collection
    {
        return collect([1, 2, 3, 4, 5])
            ->map(fn ($rating) => $reviews->where('rating', $rating)->count())
            ->values();
    }

    /**
     * 高評価書籍TOP5を生成する
     *
     * @param  Collection  $reviews  レビューコレクション
     * @return Collection 高評価書籍TOP5のコレクション
     */
    private function buildTopRatedBooks(Collection $reviews): Collection
    {
        return $reviews->where('rating', '>=', 4)
            ->sortByDesc('rating')
            ->unique('book_id')  // 同じ書籍の重複を除外
            ->take(5)
            ->map(fn ($review) => [
                'id' => $review->book->id,
                'title' => $review->book->title,
                'author' => $review->book->author,
                'rating' => $review->rating,
            ])
            ->values();
    }

    /**
     * ジャンル別評価傾向TOP5を生成する
     *
     * @param  Collection  $reviews  レビューコレクション
     * @return Collection ジャンル別評価傾向TOP5のコレクション
     */
    private function buildGenreRatings(Collection $reviews): Collection
    {
        return $reviews
            ->flatMap(fn ($review) => $review->book->genres->map(fn ($genre) => [
                'id' => $genre->id,
                'name' => $genre->name,
                'rating' => $review->rating,
            ]))
            ->groupBy('id')
            ->map(fn ($genres) => [
                'id' => $genres->first()['id'],
                'name' => $genres->first()['name'],
                'count' => $genres->count(),
                'average_rating' => $genres->avg('rating'),
            ])
            ->sortByDesc('average_rating')
            ->take(5)
            ->values();
    }
}
