<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UsersResource;
use App\Mail\Sendmail;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use MongoDB\Client as MongoDB;
use Throwable;

class UserController extends Controller
{

    /**
     * This Function is creating a Token for the authenticated user.
     *
     */

    function createToken($data)
    {
        $key = "SocialCamp";
        $payload = array(
            "iss" => "http://127.0.0.1:8000",
            "aud" => "http://127.0.0.1:8000/api",
            "iat" => time(),
            "nbf" => 1357000000,
            "id" => $data,
            'token_type' => 'bearer',
            // 'expires_in' => auth()->factory()->getTTL() * 60,
        );
        $token = JWT::encode($payload, $key, 'HS256');
        return $token;
    }

    public function register(RegisterUserRequest $request)
    {
        try {
            //DB Connection
            $collection = (new MongoDB())->MongoApp->users;

            // Validate the user inputs
            $request->validated();
            
            //check user already exists or not
            $user_exits = $collection->findOne(['email' => $request->email]);

            if (!isset($user_exits)) {
                //create a link to varify email.
                $verification_token = $this->createToken($request->email);
                $url = "http://localhost:8000/api/emailVerify/" . $verification_token . '/' . $request->email;

                //create new User in DB
                $collection->insertOne([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'verification_token' => $verification_token,
                    'email_verified_at' => null,
                ]);

                //send Email
                Mail::to($request->email)->send(new Sendmail($url, 'bevegak100@d3ff.com'));
                //call a user resource for json message. 
                return new UsersResource($request);
            } else {
                return response([
                    'Status' => '200',
                    "message" => "Email Already Exists"
                ], 200);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    //create function to verify the email
    function EmailVerify($token, $email)
    {
        try {
            //DB Connection
            $collection = (new MongoDB())->MongoApp->users;
            //find email to check is it verified or not
            $V_email = $collection->findOne(['email' => $email]);

            $emailVerify1 = $collection->findOne(['email' => $email]);

            if ($emailVerify1['email'] != $email) {
                return response([
                    'message' => 'User Does not Exits'
                ]);
            }

            $emailVerify = iterator_to_array($emailVerify1);

            if ($emailVerify['verification_token'] != $token) {
                return response([
                    'message' => 'you are not authorize'
                ]);
            }

            if ($V_email->email_verified_at != null) {
                return response([
                    'message' => 'Already Varified'
                ]);
            } elseif ($V_email) {
                $collection->updateOne(
                    ['email' => $email],
                    ['$set' => ['email_verified_at' =>  date('Y-m-d h:i:s')]]
                );

                return response([
                    'message' => 'Thankyou Your Eamil Verified NOW !!!'
                ]);
            } else {

                return response([
                    'message' => 'Something Went Wrong'
                ]);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    // Login Method
    public function login(LoginUserRequest $request)
    {
        try {
            $request->validated();

            //DB Connection
            $collection = (new MongoDB())->MongoApp->users;
            $user = $collection->findOne(['email' => $request->email]);

            if ($user['email_verified_at'] == null) {
                return response([
                    'Status' => '400',
                    'message' => 'Bad Request',
                    'Error' => 'Please Verify your Email before login'
                ], 400);
            } else {
                //check the user in DB and varify if it is authenticated or not
                if ($request->email == $user['email'] and Hash::check($request->password, $user['password'])) {

                    // check if user is already loggedin and assigned token 
                    $collection = (new MongoDB())->MongoApp->users;
                    $user = $collection->findOne(['_id' => $user['_id']]);

                    if (isset($user)) {
                        $new_token = $this->createToken($user->_id);
                        // save token in db to user 
                        $token_save =  $collection->updateOne(
                            ['_id' => $user['_id']],
                            ['$set' => ['token' => $new_token]]
                        );
                        return response([
                            'Status' => '200',
                            'Message' => 'Successfully Login',
                            'user_id' => $user->_id,
                            'Email' => $request->email,
                            'token' => $new_token
                        ], 200);
                    }
                } else {
                    return response([
                        'Status' => '400',
                        'message' => 'Bad Request',
                        'Error' => 'Email or Password doesnot match'
                    ], 400);
                }
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function Logout(Request $request)
    {
        try {
            $getToken = $request->bearerToken();

            $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));

            $userID = $decoded->id;

            $collection = (new MongoDB())->MongoApp->users;

            $encode = json_encode($userID);
            $decoded = json_decode($encode, true);
            $str_decode = $decoded['$oid'];

            $user = $collection->findOne(['_id' => new \MongoDB\BSON\ObjectID($str_decode)]);

            if ($collection->findOne(['token' => null])) {

                return response([
                    "message" => "This user is already logged out"
                ], 404);
            }

            $userExist = $collection->updateOne(
                ['_id' => $user['_id']],
                ['$set' => ['token' => null]]
            );

            return response([
                "message" => "logout successfully"
            ], 200);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function profile(Request $request)
    {
        try {
            //get token from header
            $getToken = $request->bearerToken();

            //DB Connection
            $collection = (new MongoDB())->MongoApp->users;
            $check = $collection->findOne(['token' => $getToken]);

            // if token is invalid
            if (!isset($check->token)) {
                return response([
                    "message" => "Invalid Token"
                ], 200);
            }

            $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
            $userID = $decoded->id;

            //to change token into string from array
            $encode = json_encode($userID);
            $decoded = json_decode($encode, true);
            $str_decode = $decoded['$oid'];

            if ($userID) {
                $profile = $collection->findOne(['_id' => new \MongoDB\BSON\ObjectID($str_decode)]);

                return response([
                    "Details" => $profile
                ], 200);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    // Update user profile
    public function update(Request $request)
    {
        try {
            //get token from header
            $getToken = $request->bearerToken();

            //DB Connection
            $collection = (new MongoDB())->MongoApp->users;
            // $check = $collection->findOne(['token' => ['token' => $getToken]]);

            $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
            $userID = $decoded->id;

            //to change token into string from array
            $encode = json_encode($userID);
            $decoded = json_decode($encode, true);
            $str_decode = $decoded['$oid'];

            $userupdate = $collection->findOne(['_id' => new \MongoDB\BSON\ObjectID($str_decode)]);

            $update_single_field = [];

            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['name', 'password'])) {
                    $update_single_field[$key] = $value;
                }
            }

            if (isset($update_single_field['password']) != null) {
                if ($request->password != null) {
                    $update_single_field['password'] = Hash::make($request->password);
                }
            }

            //message on Successfully
            if (isset($userupdate)) {

                $collection->updateOne(
                    ['_id' => new \MongoDB\BSON\ObjectID($str_decode)],
                    ['$set' => $update_single_field]
                );
                return response([
                    'Status' => '200',
                    'message' => 'you have successfully Update User Profile',
                ], 200);
            } else {
                return response([
                    'Status' => '200',
                    'message' => 'User not found',
                ], 404);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    // //delete User Function if You want to delete the Registered User
    /**
     * public function destroy_User($id)
     * {
     * if (User::where('id', '=', $id)->delete($id)) {
     * return response([
     * 'Status' => '200',
     * 'message' => 'you have successfully Deleted Entry',
     *  'Deleted User ID' => $id
     * ], 200);
     * } else {
     * return response([
     *  'Status' => '201',
     * 'message' => 'This User Does not Exits'
     * ], 200);
     * }
     * }
     *
     */
}
