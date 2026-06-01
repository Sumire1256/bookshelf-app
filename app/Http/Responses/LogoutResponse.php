<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    /**
     * ログアウト後のレスポンスを返す
     *
     * @param  Request  $request  ログアウトリクエスト
     * @return RedirectResponse ログインページへのリダイレクトレスポンス
     */
    public function toResponse($request): RedirectResponse
    {
        return redirect()->route('login');
    }
}
