<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use MongoDB\Client as MongoDB;
use Throwable;

class CommentController extends Controller
{
    public function create(Request $request, $id)
    {
        try {
            $getToken = $request->bearerToken();
            $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
            $userID = $decoded->id;

            $request->validate([
                'comment' => 'required'
            ]);

            //DB Connection
            $collection = (new MongoDB())->MongoApp->comments;
            $post_collection = (new MongoDB())->MongoApp->posts;

            //to change token into string from array
            $encode = json_encode($userID);
            $decoded = json_decode($encode, true);
            $str_decode = $decoded['$oid'];

            $comment = $post_collection->findOne(['_id' => new \MongoDB\BSON\ObjectID($id), 'status' => 'public']);

            $private = $post_collection->findOne(['_id' => new \MongoDB\BSON\ObjectID($id), 'status' => 'private']);

            if (isset($comment)) {
                $comment_store = $collection->insertOne(
                    [
                        'user_id' => $str_decode,
                        'post_id' => $id,
                        'comment' => $request->comment,
                        'attachment' => $request->attachment
                    ]
                );

                if (isset($comment_store)) {
                    return response([
                        'message' => 'Comment Created Succesfully',
                        // 'Comment' => $comment_store,
                    ]);
                } else {
                    return response([
                        'message' => 'Something Went Wrong While added Comment',
                    ]);
                }
            } elseif (isset($private)) {
                return response([
                    'message' => 'This Post is Private',
                ]);
            } else {
                return response([
                    'message' => 'No Post Found',
                ]);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }


    public function update(Request $request, $id)
    {
        try{
            //get token from header and check user id
            $getToken = $request->bearerToken();
            $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
            $userID = $decoded->id;

            //DB Connection
            $collection = (new MongoDB())->MongoApp->comments;

            //to change token into string from array
            $encode = json_encode($userID);
            $decoded = json_decode($encode, true);
            $str_decode = $decoded['$oid'];

            // $update_comment = Comment::all()->where('user_id', $userID)->where('id', $id)->first();
            $update_comment = $collection->findOne(['user_id' => $str_decode]);

            $not_exists = $collection->find(['_id' =>  new \MongoDB\BSON\ObjectID($id)]);

            if ($not_exists->toArray() == null) {
                return response([
                    'message' => 'Comment Not Exits',
                ]);
            }

            if (isset($update_comment)) {

                $collection->updateOne(
                    ['_id' => new \MongoDB\BSON\ObjectID($id)],
                    ['$set' => [
                        'comment' => $request->comment,
                        'attachment' => $request->attachment
                    ]]
                );

                //message on Successfully
                return response([
                    'Status' => '200',
                    'message' => 'you have successfully Update Comment',
                ], 200);
            } else {
                //message on Unauthorize
                return response([
                    'Status' => '200',
                    'message' => 'you are not Authorize to Update this Comment',
                ], 200);
            }
        }catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function delete(Request $request, $id)
    {
        try{
            $getToken = $request->bearerToken();
            $decoded = JWT::decode($getToken, new Key("SocialCamp", "HS256"));
            $userID = $decoded->id;

            //DB Connection
            $collection = (new MongoDB())->MongoApp->comments;

            //to change token into string from array
            $encode = json_encode($userID);
            $decoded = json_decode($encode, true);
            $str_decode = $decoded['$oid'];


            //than find the posts of user_id and get the specific user
            $not_exists = $collection->find(['_id' =>  new \MongoDB\BSON\ObjectID($id)]);

            //first compare user_id to the db user id 
            $comment = $collection->findOne(['user_id' => $str_decode]);

            if ($not_exists->toArray() == null) {
                return response([
                    'message' => 'Comment Not Exits',
                ]);
            }

            if (isset($comment)) {
                $collection->deleteOne(['_id' => new \MongoDB\BSON\ObjectID($id)]);

                // $comment->delete();
                return response([
                    'message' => 'Comment has been Deleted',
                ]);
            } else {
                return response([
                    'message' => 'You Unauthorize to Delete Comment',
                ]);
            }
        }
        catch (Throwable $e) {
            return $e->getMessage();
        }
}
