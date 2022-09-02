<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use DB;

class UserController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'Wrong Email/Password', 'status' => 'FAIL']);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'could_not_create_token', 'status' => 'FAIL'], 500);
        }

        $user = JWTAuth::user();

        $user->token = $token;

        $user->save();

        return response()->json(['user' => $user, 'message' => 'You have successfully Login.', 'success' => true], 200);
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'mobile_number' => 'unique:users',
                'password' => ['required','string',Password::min(6)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                ],
            ]);

            if($validator->fails()){
                $errorString = implode(",", $validator->messages()->all());
                return response()->json([
                    'status' => 'FAIL',
                    'message' => $errorString
                ]);
            }

            DB::beginTransaction();

            $user = User::create([
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'email' => $request->get('email'),
                'mobile_number' => $request->get('mobile_number'),
                'birth_date' => $request->get('birth_date'),
                'password' => Hash::make($request->get('password')),
            ]);

            DB::commit();

            $token = JWTAuth::fromUser($user);

            return response()->json(['user' => $user, 'message' => 'You have successfully signup.', 'success' => true], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            $errors['success'] = false;
            $errors['message'] = "Something went wrong !";
            if ($request->debug_mode == 'ON') {
                $errors['debug'] = $th->getMessage();
            }
            return response()->json($errors, 401);
        }

    }
}
