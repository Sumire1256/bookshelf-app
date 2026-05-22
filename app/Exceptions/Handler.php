<?php

namespace App\Exceptions;

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
     */
    public function render($request, Throwable $exception): JsonResponse|Response|RedirectResponse
    {
        if ($exception instanceof ModelNotFoundException && $request->is('api/*')) {
            return response()->json([
                'message' => '書籍が見つかりません',
            ], 404);
        }

        return parent::render($request, $exception);
    }
}
