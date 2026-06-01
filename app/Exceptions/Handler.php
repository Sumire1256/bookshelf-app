<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * 例外をHTTPレスポンスに変換する
     *
     * @param  Request  $request  リクエスト
     * @param  Throwable  $exception  発生した例外
     * @return JsonResponse|Response|RedirectResponse 例外に応じたHTTPレスポンス
     */
    public function render($request, Throwable $exception): JsonResponse|Response|RedirectResponse
    {
        if ($exception instanceof ModelNotFoundException && $request->is('api/*')) {
            return response()->json([
                'message' => '書籍が見つかりません',
            ], 404);
        }

        if ($exception instanceof AuthenticationException && $request->is('api/*')) {
            return response()->json([
                'message' => '認証が必要です',
            ], 401);
        }

        if ($exception instanceof AuthorizationException && $request->is('api/*')) {
            return response()->json([
                'message' => 'この操作を行う権限がありません',
            ], 403);
        }

        return parent::render($request, $exception);
    }
}
