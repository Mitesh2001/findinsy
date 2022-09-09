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
        try{
            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json(['message' => 'User not Found !', 'success' => false], 500);
            }

            if ($user->otp == $request->otp) {

                try {

                    $token = JWTAuth::fromUser($user);

                } catch (JWTException $e) {

                    return response()->json(['message' => 'could_not_create_token', 'success' => false], 500);

                }

            } else {

                return response()->json(['message' => 'Wrong OTP !', 'success' => false], 500);

            }

            $user->token = $token;
            $user->otp = null;
            $user->save();

            return response()->json(['user' => $user, 'message' => 'You have successfully Login.', 'success' => true], 200);

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

    public function sendOtp(Request $request)
    {

        try{

            $user = User::where('mobile_number',$request->mobile_number)->first();

            if (!$user) {
                return response()->json(['message' => 'User not Found !', 'status' => 'FAIL'], 500);
            }

            $mobile_number = "+91".(int)$request->mobile_number;
            $otp = random_int(100000, 999999);
            $user->update(['otp' => $otp]);

            $service_plan_id = "3c5760b6570345c59cad9551b88e6fc2";
            $bearer_token = "1eb6824cc52940d589c9faa2448403bc";

            $send_from = "+447520650891";
            $message = "$otp is one time password for your login in FindInsy app. DO NOT SHARE this code with anyone. Thank you !";
            $recipient_phone_numbers = $mobile_number;

            if(stristr($recipient_phone_numbers, ',')){
                $recipient_phone_numbers = explode(',', $recipient_phone_numbers);
            }else{
                $recipient_phone_numbers = [$recipient_phone_numbers];
            }

            $content = [
                'to' => array_values($recipient_phone_numbers),
                'from' => $send_from,
                'body' => $message
            ];

            $data = json_encode($content);

            $ch = curl_init("https://us.sms.api.sinch.com/xms/v1/{$service_plan_id}/batches");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BEARER);
            curl_setopt($ch, CURLOPT_XOAUTH2_BEARER, $bearer_token);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $result = curl_exec($ch);

            if(!curl_errno($ch)) {
                return response()->json(['user_id' => $user->id, 'message' => 'OTP sent successfully !', 'success' => true], 200);
            } else {
                return response()->json(['message' => 'Something went wrong !', 'success' => false], 200);
            }

            curl_close($ch);

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

    public function register(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'string|max:255',
                'email' => 'string|email|max:255',
                'mobile_number' => 'unique:users',
                'profile_pic' => 'mimetypes:image/*'
            ]);

            if($validator->fails()){
                $errorString = implode(",", $validator->messages()->all());
                return response()->json([
                    'success' => false,
                    'message' => $errorString
                ]);
            }

            DB::beginTransaction();

            $profile_pic = "";

            if ($request->hasfile('profile_pic')) {

                $imageFile = $request->file('profile_pic');
                $name = $imageFile->getClientOriginalName();
                $imageFile->move(public_path().'/profile_pictures/',$name);

                $profile_pic = '/profile_pictures/'.$name;
            }

            $user = User::create([
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'email' => $request->get('email'),
                'mobile_number' => $request->get('mobile_number'),
                'birth_date' => $request->get('birth_date'),
                'profile_pic' => $profile_pic
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

    public function getAllUsers()
    {
        try {
            $users = User::all(['id','first_name','last_name','email','mobile_number','birth_date','profile_pic','created_at','updated_at']);
            return response()->json([
                'users' => $users,
                'success' => true,
                'message' => "All user's details fetched !"
            ]);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout, please try again.'
            ]);
        }
    }

    public function logout(Request $request)
    {

        $user = JWTAuth::parseToken()->authenticate();
        if ($user) {
            $user->token = null;
            $user->save();
        }

        try {
            JWTAuth::invalidate($request->input('token'));
            return response()->json([
                'success' => true,
                'message' => "You have successfully logged out."
            ]);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout, please try again.'
            ]);
        }
    }

}
