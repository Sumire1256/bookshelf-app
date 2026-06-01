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
        $user = auth()->user();

        if ($user->likedReviews->contains($review->id)) {
            $user->likedReviews()->detach($review->id);
        } else {
            $user->likedReviews()->attach($review->id);
        }

        return back();
    }
}
