<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use App\Http\Requests\Api\RegisterRequest;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return ApiResponse::unprocessableEntity('Validation errors', $validator->errors());
        }
        $identity = $request->input('identity');
        $password = $request->input('password');
        $field = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if (!$token = auth()->attempt([$field => $identity, 'password' => $password])) {
            return ApiResponse::unauthorized();
        }

        return ApiResponse::success($this->createNewToken($token));
    }
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create(array_merge(
                $request->validated(),
                ['password' => bcrypt($request->password)]
            ));

            return ApiResponse::success($user, 'User successfully registered', 201);
        } catch (\Exception $e) {
            return ApiResponse::serverError('Server Error!');
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return ApiResponse::success(null, 'User successfully signed out');
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
       return  ApiResponse::success(auth()->user()->loadCount('posts'));
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 600,
            'user' => auth()->user()
        ];
    }
}
