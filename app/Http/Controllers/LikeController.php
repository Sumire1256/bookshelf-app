<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class LikeController extends Controller
{
    /**
     * レビューにいいねをする・いいねを取り消す
     *
     * @param  Review  $review  reviewモデルのインスタンス
     * @return RedirectResponse 前のページにリダイレクトするレスポンス
     */
    public function like(Review $review): RedirectResponse
    {
        // toggle()メソッドを使用して、いいねの状態を切り替える
        auth()->user()->likedReviews()->toggle($review->id);

        // if (auth()->user()->likedReviews->contains($review->id)) {
        // auth()->user()->likedReviews()->detach($review->id);
        // } else {
        // auth()->user()->likedReviews()->attach($review->id);
        // }

        return back();
    }
}
