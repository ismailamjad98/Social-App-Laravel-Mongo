<?php

namespace App\Http\Controllers;

use App\Models\Friend_Request as ModelsFriend_Request;
use Illuminate\Http\Request;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use MongoDB\Client as MongoDB;
use Throwable;

class Friend_Request extends Controller
{
    //
    public function Send_Friend_Request(Request $request)
    {
        try {
            //get token from header and check user id
            $getToken = $request->bearerToken();
            $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
            $userID = $decoded->id;

            //DB Connection
            $collection = (new MongoDB())->MongoApp->users;
            $Friend_request = (new MongoDB())->MongoApp->requests;

            $request->validate(
                [
                    'reciver_id' => 'required'
                ]
            );

            //to change token into string from array
            $encode = json_encode($userID);
            $decoded = json_decode($encode, true);
            $str_decode = $decoded['$oid'];

            if ($str_decode == $request->reciver_id) {
                return response([
                    "Message" => "You cannot Send Friend Request to yourself"
                ]);
            }

            //check if recever_user is exists in Users_table DB
            $users_table = $collection->findOne(['_id' => new \MongoDB\BSON\ObjectId($request->reciver_id)]);

            //chcek if user is already sended request or not
            $check_alreadySent =  $Friend_request->findOne([
                'sender_id' => $str_decode,
                'reciver_id' => $request->reciver_id,
            ]);

            if (isset($check_alreadySent)) {
                return response([
                    "Message" => "You have already Sent the Friend Request to this User"
                ]);
            }

            if (isset($users_table)) {
                $Friend_request->insertOne([
                    'sender_id' => $str_decode,
                    'reciver_id' => $request->reciver_id,
                    'status' => 0
                ]);

                return response([
                    "Message" => "You have Successfully Send Friend Request "
                ]);
            } else {
                return response([
                    "Message" => "This User Doesnot Exists in Records"
                ]);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function My_Requests(Request $request)
    {
        try {
            //get token from header and check user id
            $getToken = $request->bearerToken();
            $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
            $userID = $decoded->id;

            //to change token into string from array
            $encode = json_encode($userID);
            $decoded = json_decode($encode, true);
            $str_decode = $decoded['$oid'];

            //DB Connection
            $Friend_request = (new MongoDB())->MongoApp->requests;

            $req = $Friend_request->findOne([
                'reciever_id' => $str_decode,
                'status' => 0
            ]);

            if (json_decode($req) != null) {
                return response([
                    "All Requests" => $req,
                ]);
            } else {
                return response([
                    'message' => 'You Dont have any Friend Request'
                ], 404);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function Accept_Request(Request $request)
    {
        try {

            $request->validate(
                [
                    'sender_id' => 'required'
                ]
            );

            //DB Connection
            $Friend_request = (new MongoDB())->MongoApp->requests;

            //get token from header and check user id
            $getToken = $request->bearerToken();
            $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
            $userID = $decoded->id;

            //to change token into string from array
            $encode = json_encode($userID);
            $decoded = json_decode($encode, true);
            $str_decode = $decoded['$oid'];

            if ($str_decode == $request->sender_id) {
                return response([
                    "Message" => "You cannot Receive Friend Request of yourself"
                ]);
            }

            $requestCollection = (new MongoDB())->social_app->requests;
            $recive_req =  $requestCollection->findOne(
                [
                    'sender_id' => $request->sender_id,
                    'reciver_id' => $str_decode,
                ]
            );

            //check if recever_user is exists in Request Table DB

            if (!$recive_req) {
                return response([
                    "Message" => "You do not have any friend request from this user"
                ]);
            }

            if ($recive_req->status == '1') {
                return response([
                    "Message" => "You are already Friend of this User"
                ]);
            }

            if (isset($recive_req)) {
                $update_status = $requestCollection->updateOne(
                    ['_id' => new \MongoDB\BSON\ObjectID($str_decode)],
                    ['$set' => [
                        'status' => 1
                    ]]
                );
                return response([
                    "Message" => "Congratulations! You are Friends Now"
                ]);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function Delete_Request(Request $request, $id)
    {
        try {
            //DB Connection
            $Friend_request = (new MongoDB())->MongoApp->requests;

            //get token from header and check user id
            $getToken = $request->bearerToken();
            $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
            $userID = $decoded->id;

            //to change token into string from array
            $encode = json_encode($userID);
            $decoded = json_decode($encode, true);
            $str_decode = $decoded['$oid'];

            $delete_request = $Friend_request->findOne(['reciver_id' => $str_decode, 'sender_id' => $id]);

            if (isset($delete_request)) {
                $Friend_request->deleteOne(['_id' => new \MongoDB\BSON\ObjectID($id)]);
                // $delete_request->delete($id);
                return response([
                    'Status' => '200',
                    'message' => 'you have successfully Delete Friend Request',
                    'Deleted Post ID' => $id
                ], 200);
            } else {
                return response([
                    'Status' => '201',
                    'message' => 'This User Not send Request'
                ], 200);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}
