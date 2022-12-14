<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Validation\UnauthorizedException;
use PhpParser\Node\Stmt\TryCatch;
use JWTAuth;
use function PHPUnit\Framework\throwException;

class AuthController extends Controller
{

    public function showRegisterForm()
    {
        return view('backend.auth.register');
    }

    public function register(Request $request)
    {
        $check = User::query()->count('id');
        if ($check < 1) {
            User::create([
                'firstName' => $request->input('firstName'),
                'lastName' => $request->input('lastName'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password'))
            ]);
            $user = User::query()->find(1);
            $user->roles()->sync(1);
            return response()->json(['message' => 'Đăng kí thành công !'],200);
        }else {
            return response()->json(['message' => 'Bạn không có quyền đăng kí !'],500);
        }
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        try{
            $token = JWTAuth::attempt($credentials);
            if ($token)
            {
                return $this->respondWithToken($token);
            }else{
               throw new UnauthorizedException();
            }

        }catch (UnauthorizedException $exception) {
          return response()->json(['error' => 'Email hoặc mật khẩu chưa chính xác !'], 401);
        }
    }

    public function logout()
    {
        auth()->logout();
        return response('Ok');
    }

    public function user()
    {
        return User::with('profile','departments','roles')->find(auth()->id());
    }

    protected function respondWithToken($token): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ],200);
    }

}
