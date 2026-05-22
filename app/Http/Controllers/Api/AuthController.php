<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * APIトークンを発行する
     *
     * @param  Request  $request  メールアドレスとパスワード
     */
    public function login(Request $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'メールアドレスまたはパスワードが正しくありません',
            ], 401);
        }

        return response()->json([
            'token' => $user->createToken('api-token')->plainTextToken,
        ]);
    }

    /**
     * APIトークンを削除する
     *
     * @param  Request  $request  リクエスト
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'ログアウトしました',
        ]);
    }
}
